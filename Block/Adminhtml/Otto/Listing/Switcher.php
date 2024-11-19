<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing;

use M2E\Otto\Block\Adminhtml\Listing\Switcher as AbstractSwitcher;

class Switcher extends AbstractSwitcher
{
    public function _construct()
    {
        parent::_construct();

        $this->setAddListingUrl('*/otto_listing_create/index');
    }
}
