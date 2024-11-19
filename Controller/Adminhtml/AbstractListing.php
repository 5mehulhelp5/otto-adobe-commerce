<?php

namespace M2E\Otto\Controller\Adminhtml;

abstract class AbstractListing extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::listings');
    }
}
