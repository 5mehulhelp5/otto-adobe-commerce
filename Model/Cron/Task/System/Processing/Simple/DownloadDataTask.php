<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\System\Processing\Simple;

class DownloadDataTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'processing/simple/download/data';

    private \M2E\Otto\Model\Processing\RetrieveData\Simple $retrieveDataSimple;

    public function __construct(
        \M2E\Otto\Model\Processing\RetrieveData\Simple $retrieveDataSimple
    ) {
        $this->retrieveDataSimple = $retrieveDataSimple;
    }

    public function process($context): void
    {
        $this->retrieveDataSimple->process();
    }
}
