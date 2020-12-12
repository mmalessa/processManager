<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class AddAmqpStampMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var SagaMessage $sagaMessage */
        $sagaMessage = $envelope->getMessage();
        $message = $sagaMessage->getMessage();

        $messageClassName = (string)get_class($message);
        $routingKey = $this->getRoutingKeyFromClassName($messageClassName);

        $attributes = [
            'delivery_mode' => AMQP_DURABLE,
        ];
        if(defined("$messageClassName::PRIORITY")) {
            $attributes['priority'] = $messageClassName::PRIORITY;
        }
        $envelope = $envelope->with(new AmqpStamp($routingKey, AMQP_NOPARAM, $attributes));
        return $stack->next()->handle($envelope, $stack);
    }

    private function getRoutingKeyFromClassName(string $className): string
    {
        $messageType = SagaMessage::getMessageTypeFromClassName($className);
        return str_replace('\\', '.', $messageType);
    }
}
