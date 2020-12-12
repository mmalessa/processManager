<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Exception\RuntimeException;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class AMQPMessageSerializer implements SerializerInterface
{
    private string $targetBusName;
    private LoggerInterface $logger;

    public function __construct(
        string $targetBusName,
        LoggerInterface $logger
    )
    {
        $this->targetBusName = $targetBusName;
        $this->logger = $logger;
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
        $headers = $encodedEnvelope['headers'];
        try {
            $sagaMessage = SagaMessage::deserialize($body);
            $envelope = new Envelope($sagaMessage);
            $retryCount = $this->getRetryCount($headers);
        } catch (\Exception $e) {
            $this->logger->error(sprintf("Decode AMQP Envelope: %s", $e->getMessage()));
            $sagaMessage = new SagaMessage(
                'FailureMessage',
                'Failures',
                new FailureMessage($encodedEnvelope)
            );
            $envelope = new Envelope($sagaMessage);
            $retryCount = 999;
        }
        $envelope = $envelope->with(new RedeliveryStamp($retryCount));
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
