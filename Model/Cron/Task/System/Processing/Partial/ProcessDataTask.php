<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\System\Processing\Partial;

class ProcessDataTask extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'processing/partial/process/data';

    private \M2E\Otto\Model\Processing\ProcessResult\Partial $processResultPartial;
    private \M2E\Otto\Model\Processing\Lock\ClearMissed $lockClearMissed;

    public function __construct(
        \M2E\Otto\Model\Processing\ProcessResult\Partial $processResultPartial,
        \M2E\Otto\Model\Processing\Lock\ClearMissed $lockClearMissed,
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

        $this->processResultPartial = $processResultPartial;
        $this->lockClearMissed = $lockClearMissed;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->processResultPartial->processExpired();

        $this->processResultPartial->processData();

        $this->lockClearMissed->process();
    }
}
