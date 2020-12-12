<?php
declare(strict_types=1);

namespace App\UI\Command\Dev;

use App\Domain\Message\Sophie\Command\BuyBeer;
use App\Domain\Message\Sophie\Command\BuyCookies;
use App\Domain\Message\Sophie\Event\CookiesBought;
use App\Domain\Saga\HomeShopping;
use App\Infrastructure\Messenger\SagaMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TryCommand extends Command
{
    protected static $defaultName = 'app:dev:try';
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Dev-Try");

        $sagaId = 'testSagaId';
        $sagaType = HomeShopping::SAGA_TYPE;

//        $message = new BuyCookies("Lajkonik", 3);
//        $message = new CookiesBought();
        $message = new BuyBeer(['Perła Chmielowa', 'Perła Export']);
        $sagaMessage = new SagaMessage($sagaId, $sagaType, $message);

        $this->messageBus->dispatch($sagaMessage);

        return Command::SUCCESS;
    }
}
