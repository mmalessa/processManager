<?php
declare(strict_types=1);

namespace App\Domain\Message\Sophie\Command;

use App\Domain\Message\MessageInterface;

class BuyCookies implements MessageInterface
{
    public string $name;
    public int $number;

    public function __construct(string $name, int $number)
    {
        $this->name = $name;
        $this->number = $number;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}
