<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Account;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractAccount;

class AccountGrid extends AbstractAccount
{
    public function execute()
    {
        /** @var \M2E\Otto\Block\Adminhtml\Otto\Account\Grid $switcherBlock */
        $grid = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Account\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
