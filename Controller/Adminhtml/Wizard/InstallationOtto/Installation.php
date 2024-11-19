<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class Installation extends \M2E\Otto\Controller\Adminhtml\Wizard\AbstractInstallation
{
    public function execute()
    {
        return $this->installationAction();
    }
}
