<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

use M2E\Otto\Model\Order\Change;

class UpdateTask extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'order/update';

    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private Change\Repository $changeRepository;
    /** @var \M2E\Otto\Model\Order\Change\ShipmentProcessor */
    private Change\ShipmentProcessor $shipmentProcessor;

    public function __construct(
        \M2E\Otto\Model\Order\Change\ShipmentProcessor $shipmentProcessor,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Order\Change\Repository $changeRepository,
        \M2E\Otto\Model\Cron\Manager $cronManager,
        \M2E\Otto\Model\Synchronization\LogService $syncLogger,
        \M2E\Otto\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Otto\Helper\Factory $helperFactory,
        \M2E\Otto\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $helperFactory,
            $taskRepo,
            $resource
        );
        $this->accountRepository = $accountRepository;
        $this->changeRepository = $changeRepository;
        $this->shipmentProcessor = $shipmentProcessor;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\Otto\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();
        $synchronizationLog->setTask(\M2E\Otto\Model\Synchronization\Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    protected function performActions(): void
    {
        $this->deleteNotActualChanges();

        foreach ($this->accountRepository->getAll() as $account) {
            $this->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');

            try {
                $this->shipmentProcessor->process($account);
            } catch (\Throwable $exception) {
                $message = (string)__(
                    'The "Update" Action for Account "%1" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
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
