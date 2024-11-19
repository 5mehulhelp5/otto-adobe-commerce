<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Wizard\InstallationOtto;

class ListingTemplates extends Installation
{
    public function execute()
    {
        return $this->_redirect('*/otto_listing_create', ['step' => 2, 'wizard' => true]);
    }
}
