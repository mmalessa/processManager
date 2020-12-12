<?php
declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use App\Domain\Message\MessageInterface;
use App\Domain\Message\Sophie\Command\BuyCookies;

class SagaMessage
{
    private string $sagaId;
    private string $sagaType;
    private MessageInterface $message;
    private const MESSAGE_CLASS_PREFIX = "App\Domain";

    public function __construct(string $sagaId, string $sagaType, MessageInterface $message)
    {
        $this->sagaId = $sagaId;
        $this->sagaType = $sagaType;
        $this->message = $message;
    }

    public function serialize(): string
    {
        $arrayMessage = [
            'sagaId' => $this->sagaId,
            'sagaType' => $this->sagaType,
            'messageType' => $this->getMessageType(),
            'message' => $this->getSerializedMessage()
        ];
        return json_encode($arrayMessage);
    }

    private function getSerializedMessage(): array
    {
        $reflection = new \ReflectionClass(get_class($this->message));
        $constructor = $reflection->getConstructor();
        if (null === $constructor) {
            return [];
        }
        $parameters = $constructor->getParameters();
        $serializedMessage = [];
        /** @var \ReflectionParameter $parameter */
        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();
            $methodName = sprintf("get%s", ucfirst($parameterName));
            $parameterValue = $this->message->{$methodName}();
            $serializedMessage[$parameterName] = $parameterValue;
        }
        return $serializedMessage;
    }

    private function getMessageType(): string
    {
        $className = get_class($this->message);
        $pattern = sprintf("/^%s/", preg_quote(static::MESSAGE_CLASS_PREFIX));
        return preg_replace($pattern, '', $className);
    }

    public static function deserialize(string $serializedMessage): self
    {
        $arrayMessage = json_decode($serializedMessage, true);
        $sagaId = $arrayMessage['sagaId'];
        $sagaType = $arrayMessage['sagaType'];
        $messageType = $arrayMessage['messageType'];
        $messageArray = $arrayMessage['message'];
        $message = static::deserializeMessage($messageType, $messageArray);
        return new self($sagaId, $sagaType, $message);
    }

    private static function deserializeMessage(string $messageType, array $arrayMessage): MessageInterface
    {
        $className = sprintf("%s%s", static::MESSAGE_CLASS_PREFIX, $messageType);
        $classParameters = [];
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        if (null !== $constructor) {
            $parameters = $constructor->getParameters();
            foreach ($parameters as $parameter) {
                $parameterName = $parameter->getName();
                $parameterValue = $arrayMessage[$parameterName] ?? null;
                $classParameters[] = $parameterValue;
            }
        }
        //FIXME - handle errors!!!!
        /** @var MessageInterface $message */
        $message = $reflection->newInstanceArgs($classParameters);
        return $message;
    }


}
