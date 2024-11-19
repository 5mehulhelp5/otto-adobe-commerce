<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Template;

abstract class AbstractCategory extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractMain
{
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_Otto::configuration_categories');
    }
}
