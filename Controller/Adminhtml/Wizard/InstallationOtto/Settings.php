<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class Settings extends Installation
{
    public function execute()
    {
        $this->init();

        return $this->renderSimpleStep();
    }
}
