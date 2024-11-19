<?php

namespace M2E\Otto\Controller\Adminhtml;

abstract class AbstractGeneral extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::otto');
    }
}
