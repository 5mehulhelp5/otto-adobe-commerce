<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task;

class InventorySyncTask extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'inventory/sync';

    private const SYNC_INTERVAL_8_HOURS_IN_SECONDS = 28800;

    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Processing\Runner $processingRunner;
    private \M2E\Otto\Model\Processing\Lock\Repository $lockRepository;
    private \M2E\Otto\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Processing\Runner $processingRunner,
        \M2E\Otto\Model\Processing\Lock\Repository $lockRepository,
        \M2E\Otto\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory,
        \M2E\Otto\Model\Cron\Manager $cronManager,
        \M2E\Otto\Model\Synchronization\LogService $syncLogger,
        \M2E\Otto\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Otto\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $taskRepo,
            $resource,
        );
        $this->accountRepository = $accountRepository;
        $this->processingRunner = $processingRunner;
        $this->lockRepository = $lockRepository;
        $this->processingInitiatorFactory = $processingInitiatorFactory;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function getSynchronizationLog(): \M2E\Otto\Model\Synchronization\LogService
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setTask(\M2E\Otto\Model\Synchronization\Log::TASK_OTHER_LISTINGS);
        $synchronizationLog->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        return $synchronizationLog;
    }

    protected function performActions(): void
    {
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();
        foreach ($this->accountRepository->findActiveWithEnabledInventorySync() as $account) {
            if (
                $account->getInventoryLastSyncDate() !== null
                && $account->getInventoryLastSyncDate()->modify(
                    '+ ' . self::SYNC_INTERVAL_8_HOURS_IN_SECONDS  . ' seconds',
                ) > $currentDate
            ) {
                continue;
            }

            if ($this->lockRepository->isExist(\M2E\Otto\Model\Account::LOCK_NICK, $account->getId())) {
                continue;
            }

            $this->getOperationHistory()->addText(
                "Starting Account '{$account->getTitle()} ({$account->getId()})'",
            );
            $this->getOperationHistory()->addTimePoint(
                $timePointId = __METHOD__ . 'process' . $account->getId(),
                "Process Account '{$account->getTitle()}'",
            );

            // ----------------------------------------

            try {
                $initiator = $this->processingInitiatorFactory->create($account);
                $this->processingRunner->run($initiator);
            } catch (\Throwable $e) {
                $this->getOperationHistory()
                     ->addText(
                         sprintf(
                             "Error '%s'. Message: %s",
                             $account->getTitle(),
                             $e->getMessage()
                         )
                     );
            }

            // ----------------------------------------

            $this->getOperationHistory()->saveTimePoint($timePointId);
        }
    }
}
