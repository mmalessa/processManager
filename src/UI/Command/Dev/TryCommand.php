<?php
declare(strict_types=1);

namespace App\UI\Command\Dev;

use App\Domain\Message\Sophie\Command\BuyCookies;
use App\Domain\Message\Sophie\Event\CookiesBought;
use App\Domain\Saga\HomeShopping;
use App\Infrastructure\Messenger\SagaMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TryCommand extends Command
{
    protected static $defaultName = 'app:dev:try';

    public function __construct()
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Dev-Try");

        $sagaId = 'testSagaId';
        $sagaType = HomeShopping::class;

        //$message = new BuyCookies("Lajkonik", 3);
        $message = new CookiesBought();

        $sagaMessage = new SagaMessage($sagaId, $sagaType, $message);

        $serializedMessage = $sagaMessage->serialize();
        echo "## Serialized message: " . $serializedMessage . PHP_EOL . PHP_EOL;

        $deserializedMessage = SagaMessage::deserialize($serializedMessage);
        echo "## Deserialized message: " . PHP_EOL;
        print_r($deserializedMessage);

        return Command::SUCCESS;
    }
}
