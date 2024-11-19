<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard;

abstract class AbstractRegistration extends \M2E\Otto\Controller\Adminhtml\AbstractWizard
{
    protected function getCustomViewNick()
    {
        return null;
    }

    protected function getNick()
    {
        return null;
    }

    protected function getMenuRootNodeNick()
    {
        return null;
    }

    protected function getMenuRootNodeLabel()
    {
        return null;
    }
}
