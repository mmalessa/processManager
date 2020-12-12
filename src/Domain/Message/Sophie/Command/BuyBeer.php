<?php
declare(strict_types=1);

namespace App\Domain\Message\Sophie\Command;

use App\Domain\Message\MessageInterface;

class BuyBeer implements MessageInterface
{
    private array $names;

    public function __construct(array $names)
    {
        $this->names = $names;
    }

    public function getNames(): array
    {
        return $this->names;
    }
}
