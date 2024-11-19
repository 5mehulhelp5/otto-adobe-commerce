<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing;

use M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractContainer;

class ItemsByListing extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('OttoListingItemsByListing');
        $this->_controller = 'adminhtml_otto_listing_itemsByListing';
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $url = $this->getUrl('*/otto_listing_create/index', ['step' => 1, 'clear' => 1]);
        $this->addButton('add', [
            'label' => __('Add Listing'),
            'onclick' => 'setLocation(\'' . $url . '\')',
            'class' => 'action-primary',
            'button_class' => '',
        ]);

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        /** @var \M2E\Otto\Block\Adminhtml\Otto\Listing\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(Tabs::class);
        $tabsBlock->activateItemsByListingTab();
        $tabsBlockHtml = $tabsBlock->toHtml();

        return $tabsBlockHtml . parent::_toHtml();
    }
}
