<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto;

abstract class AbstractWizard extends \M2E\Otto\Controller\Adminhtml\AbstractWizard
{
    protected function initResultPage()
    {
        if ($this->resultPage !== null) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()->setActiveMenu($this->getMenuRootNodeNick());
    }

    protected function getMenuRootNodeNick()
    {
        return \M2E\Otto\Helper\View\Otto::MENU_ROOT_NODE_NICK;
    }

    protected function getMenuRootNodeLabel()
    {
        return \M2E\Otto\Helper\View\Otto::getMenuRootNodeLabel();
    }
}
