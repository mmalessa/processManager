<?php
declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SagaMessageHandler implements MessageHandlerInterface
{
    private $messageBus;
    private $sagaStateRepository; //TODO
    private $logger; //TODO

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(SagaMessage $sagaMessage)
    {
        print_r($sagaMessage);
        $sagaId = $sagaMessage->getSagaId();
        $sagaType = $sagaMessage->getSagaType();
        $message = $sagaMessage->getMessage();

        //TODO
        /*
         * - getSagaState(sagaId)
         * - checkSagaType
         * - handle message
         * - saveSagaState
         */
    }
}
