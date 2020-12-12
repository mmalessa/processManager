<?php
declare(strict_types=1);

namespace App\Infrastructure\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SagaMessageHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;
    private $sagaStateRepository; //TODO
    private LoggerInterface $logger;

    public function __construct(
        MessageBusInterface $messageBus,
        LoggerInterface $logger
    )
    {
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function __invoke(SagaMessage $sagaMessage)
    {
        $sagaId = $sagaMessage->getSagaId();
        $sagaType = $sagaMessage->getSagaType();
        $message = $sagaMessage->getMessage();
        $messageClassName = get_class($message);

        $this->logger->info(sprintf(
            "Message received: [%s->%s] (%s)",
            $sagaType,
            $sagaId,
            SagaMessage::getMessageTypeFromClassName($messageClassName)
        ));

        if (FailureMessage::class === $messageClassName) {
            /** @var FailureMessage $failureMessage */
            $failureMessage = $message;
            $this->logger->error(sprintf(
                "Message rejected: %s",
                json_encode($failureMessage->getEncodedEnvelope())
            ));
            throw new \Exception('Failure message');
        }

        $this->logger->info(sprintf(
            "Content: %s",
            $sagaMessage->serialize()
        ));

        $this->logger->notice('There is a lot to do here...');

        //TODO
        /*
         * - getSagaState(sagaId)
         * - checkSagaType
         * - handle message
         * - saveSagaState
         */
    }


}
