<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Debug;

use M2E\Otto\Controller\Adminhtml\ControlPanel\AbstractCommand;

class Debug extends AbstractCommand
{
    /**
     * @title "First Test"
     * @description "Command for quick development"
     */
    public function firstTestAction()
    {
        return null;
    }

    /**
     * @title "Second Test"
     * @description "Command for quick development"
     */
    public function secondTestAction()
    {
        return null;
    }
}
