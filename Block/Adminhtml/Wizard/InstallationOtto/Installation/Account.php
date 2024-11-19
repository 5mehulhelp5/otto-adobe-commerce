<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Wizard\InstallationOtto\Installation;

class Account extends \M2E\Otto\Block\Adminhtml\Wizard\InstallationOtto\Installation
{
    protected function _construct(): void
    {
        parent::_construct();

        $this->removeButton('continue');
    }
    protected function getStep()
    {
        return 'account';
    }
}
