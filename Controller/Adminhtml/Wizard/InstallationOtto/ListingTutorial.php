<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class ListingTutorial extends \M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto\Installation
{
    public function execute()
    {
        $this->init();

        return $this->renderSimpleStep();
    }
}
