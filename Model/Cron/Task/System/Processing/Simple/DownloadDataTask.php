<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\System\Processing\Simple;

class DownloadDataTask extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'processing/simple/download/data';

    private \M2E\Otto\Model\Processing\RetrieveData\Simple $retrieveDataSimple;

    public function __construct(
        \M2E\Otto\Model\Processing\RetrieveData\Simple $retrieveDataSimple,
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
            $resource,
        );
        $this->retrieveDataSimple = $retrieveDataSimple;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->retrieveDataSimple->process();
    }
}
