<?php
declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use App\Domain\Message\MessageInterface;

class SagaMessage
{
    private string $sagaId;
    private string $sagaType;
    private MessageInterface $message;
    private const MESSAGE_CLASS_PREFIX = "App\Domain\Message";

    public function __construct(string $sagaId, string $sagaType, MessageInterface $message)
    {
        $this->sagaId = $sagaId;
        $this->sagaType = $sagaType;
        $this->message = $message;
    }

    public function getSagaId(): string
    {
        return $this->sagaId;
    }

    public function getSagaType(): string
    {
        return $this->sagaType;
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function serialize(): string
    {
        $arrayMessage = [
            'sagaId' => $this->sagaId,
            'sagaType' => $this->sagaType,
            'messageType' => static::getMessageTypeFromClassName(get_class($this->message)),
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

    public static function getMessageTypeFromClassName(string $messageClassName): string
    {
        $fixedPrefix = static::MESSAGE_CLASS_PREFIX;
        if (!str_ends_with('\\', $fixedPrefix)) {
            $fixedPrefix .= '\\';
        }
        $pattern = sprintf("/^%s/", preg_quote($fixedPrefix));
        return preg_replace($pattern, '', $messageClassName);
    }

    public static function getClassNameFromMessageType(string $messageType): string
    {
        $fixedPrefix = static::MESSAGE_CLASS_PREFIX;
        if (!str_ends_with('\\', $fixedPrefix)) {
            $fixedPrefix .= '\\';
        }
        return sprintf("%s%s", $fixedPrefix, $messageType);
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
        $className = static::getClassNameFromMessageType($messageType);
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
        /** @var MessageInterface $message */
        $message = $reflection->newInstanceArgs($classParameters);
        return $message;
    }


}
