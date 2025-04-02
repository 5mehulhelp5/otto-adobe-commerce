<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

use M2E\Otto\Model\Order\Change;

class UpdateTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'order/update';

    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private Change\Repository $changeRepository;
    /** @var \M2E\Otto\Model\Order\Change\ShipmentProcessor */
    private Change\ShipmentProcessor $shipmentProcessor;

    public function __construct(
        \M2E\Otto\Model\Order\Change\ShipmentProcessor $shipmentProcessor,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Order\Change\Repository $changeRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->changeRepository = $changeRepository;
        $this->shipmentProcessor = $shipmentProcessor;
    }

    /**
     * @param \M2E\Otto\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\Otto\Model\Synchronization\Log::TASK_ORDERS);

        $this->deleteNotActualChanges();

        foreach ($this->accountRepository->getAll() as $account) {
            $context->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->shipmentProcessor->process($account);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Update" Action for Account "%1" was completed with error.',
                    $account->getTitle()
                );

                $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                $context->getExceptionHandler()->processTaskException($exception);
            }
        }
    }

    private function deleteNotActualChanges(): void
    {
        $this->changeRepository->deleteByProcessingAttemptCount(
            \M2E\Otto\Model\Order\Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
        );
    }
}
