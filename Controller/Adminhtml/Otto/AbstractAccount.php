<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto;

abstract class AbstractAccount extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::configuration_accounts');
    }
}
