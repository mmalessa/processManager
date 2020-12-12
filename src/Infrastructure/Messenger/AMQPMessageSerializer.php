<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class AMQPMessageSerializer implements SerializerInterface
{
    private string $targetBusName;

    public function __construct(string $targetBusName)
    {
        $this->targetBusName = $targetBusName;
    }

    public function encode(Envelope $envelope): array
    {
        /** @var SagaMessage $sagaMessage */
        $sagaMessage = $envelope->getMessage();
        $headers = [
            'Content-Type' => 'application/json',
        ];
        return [
            'body' => $sagaMessage->serialize(),
            'headers' => $headers
        ];
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        $body = $encodedEnvelope['body'];
        //FIXME - add error handling!!!
        $sagaMessage = SagaMessage::deserialize($body);
        $envelope = new Envelope($sagaMessage);

        $headers = $encodedEnvelope['headers'];
        $retryCount = $this->getRetryCount($headers);
        $envelope = $envelope->with(new RedeliveryStamp($retryCount, get_class($sagaMessage)));
        $envelope = $envelope->with(new BusNameStamp($this->targetBusName));
        return $envelope;
    }

    private function getRetryCount(array $headers): int
    {
        if (!array_key_exists('x-death', $headers)) {
            return 0;
        }
        return array_sum(array_column($headers['x-death'],'count'));
    }
}
