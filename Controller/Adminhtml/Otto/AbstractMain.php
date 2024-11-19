<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto;

abstract class AbstractMain extends \M2E\Otto\Controller\Adminhtml\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::otto');
    }

    protected function initResultPage()
    {
        if ($this->resultPage !== null) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(\M2E\Otto\Helper\View\Otto::getTitle());

        if ($this->getLayoutType() != self::LAYOUT_BLANK) {
            $this->getResultPage()->setActiveMenu(\M2E\Otto\Helper\View\Otto::MENU_ROOT_NODE_NICK);
        }
    }
}
