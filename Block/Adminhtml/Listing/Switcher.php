<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Listing;

use M2E\Otto\Block\Adminhtml\Magento\AbstractBlock;

/**
 * Class \M2E\Otto\Block\Adminhtml\Listing\Switcher
 */
abstract class Switcher extends AbstractBlock
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setAddListingUrl('');

        $this->setTemplate('M2E_Otto::listing/switcher.phtml');
    }

    //########################################
}
