<?php
declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use App\Domain\Message\MessageInterface;

class FailureMessage implements MessageInterface
{
    private array $encodedEnvelope;
    public function __construct(array $encodedEnvelope)
    {
        $this->encodedEnvelope = $encodedEnvelope;
    }

    public function getEncodedEnvelope(): array
    {
        return $this->encodedEnvelope;
    }
}
