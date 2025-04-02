<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task;

class InventorySyncTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
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
        \M2E\Otto\Model\Listing\InventorySync\Processing\InitiatorFactory $processingInitiatorFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->processingRunner = $processingRunner;
        $this->lockRepository = $lockRepository;
        $this->processingInitiatorFactory = $processingInitiatorFactory;
    }

    /**
     * @param \M2E\Otto\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\Otto\Model\Synchronization\Log::TASK_OTHER_LISTINGS);
        $context->getSynchronizationLog()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

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

            $context->getOperationHistory()->addText(
                "Starting Account '{$account->getTitle()} ({$account->getId()})'",
            );
            $context->getOperationHistory()->addTimePoint(
                $timePointId = __METHOD__ . 'process' . $account->getId(),
                "Process Account '{$account->getTitle()}'",
            );

            // ----------------------------------------

            try {
                $initiator = $this->processingInitiatorFactory->create($account);
                $this->processingRunner->run($initiator);
            } catch (\Throwable $e) {
                $context->getOperationHistory()
                     ->addText(
                         sprintf(
                             "Error '%s'. Message: %s",
                             $account->getTitle(),
                             $e->getMessage()
                         )
                     );
            }

            // ----------------------------------------

            $context->getOperationHistory()->saveTimePoint($timePointId);
        }
    }
}
