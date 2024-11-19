<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Account;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractAccount;

class Index extends AbstractAccount
{
    public function execute()
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Accounts'));

        return $this->getResultPage();
    }
}
