<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Account;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractAccount;

class NewAction extends AbstractAccount
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
