<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Cron;

class Run extends \M2E\Otto\Controller\Adminhtml\ControlPanel\AbstractMain
{
    private \M2E\Otto\Model\Cron\Runner\Developer $cronRunner;

    public function __construct(
        \M2E\Otto\Model\Cron\Runner\Developer $cronRunner
    ) {
        parent::__construct();
        $this->cronRunner = $cronRunner;
    }

    public function execute(): void
    {
        $taskCode = $this->getRequest()->getParam('task_code');

        if (!empty($taskCode)) {
            $this->cronRunner->setAllowedTasks([$taskCode]);
        }

        $this->cronRunner->process();

        $this->getResponse()->setBody('<pre>' . $this->cronRunner->getOperationHistory()->getFullDataInfo() . '</pre>');
    }
}
