<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class Index extends Installation
{
    public function execute()
    {
        return $this->indexAction();
    }
}
