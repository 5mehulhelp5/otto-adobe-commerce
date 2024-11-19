<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Synchronization;

abstract class AbstractLog extends \M2E\Otto\Controller\Adminhtml\AbstractMain
{
    protected function initResultPage(): void
    {
        if ($this->resultPage !== null) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()->setActiveMenu($this->getMenuRootNodeNick());
    }

    protected function getMenuRootNodeNick(): string
    {
        return \M2E\Otto\Helper\View\Otto::MENU_ROOT_NODE_NICK;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_Otto::help_center_synchronization_log');
    }
}
