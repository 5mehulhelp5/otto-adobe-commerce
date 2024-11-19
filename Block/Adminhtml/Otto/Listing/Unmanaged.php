<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing;

class Unmanaged extends \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    public function _construct(): void
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ottoListingUnmanaged');
        $this->_controller = 'adminhtml_otto_listing_unmanaged';
        // ---------------------------------------

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('add');
        $this->buttonList->remove('save');
        $this->buttonList->remove('edit');
    }

    protected function _toHtml()
    {
        $this->jsUrl->addUrls([
            'mapProductPopupHtml' => $this->getUrl(
                '*/listing_other_mapping/mapProductPopupHtml',
                [
                    'account_id' => $this->getRequest()->getParam('account'),
                ]
            ),
            'listing_other_mapping/map' => $this->getUrl('*/listing_other_mapping/map'),
            'mapAutoToProduct' => $this->getUrl('*/listing_other_mapping/autoMap'),
            'otto_listing/view' => $this->getUrl('*/otto_listing/view'),

            'prepareData' => $this->getUrl('*/listing_other_moving/prepareMoveToListing'),
            'moveToListingGridHtml' => $this->getUrl('*/listing_other_moving/moveToListingGrid'),
            'createListing' => $this->getUrl('*/listing_wizard/createUnmanaged'),
            'listingWizard' => $this->getUrl('*/listing_wizard/index'),

            'removingProducts' => $this->getUrl('*/otto_listing_unmanaged/removing'),
            'unmappingProducts' => $this->getUrl('*/listing_other_mapping/unmapping'),
        ]);

        $this->jsTranslator->addTranslations([
            'Link Item "%product_title%" with Magento Product' => __(
                'Link Item "%product_title%" with Magento Product'
            ),
            'Product does not exist.' => __('Product does not exist.'),
            'Product(s) was Linked.' => __('Product(s) was Linked.'),
            'Add New Listing' => __('Add New Listing'),
            'failed_mapped' => __(
                'Some Items were not linked. Please edit <i>Product Linking Settings</i> under
            <i>Configuration > Account > Unmanaged Listings</i> or try to link manually.'
            ),
            'Product was Linked.' => __('Product was Linked.'),
            'Linking Product' => __('Linking Product'),
            'product_does_not_exist' => __('Product does not exist.'),
            'select_simple_product' => __(
                'Current Otto version only supports Simple Products in Linking. Please, choose Simple Product.'
            ),
            'automap_progress_title' => __('Link Item(s) to Products'),
            'processing_data_message' => __('Processing %product_title% Product(s).'),
            'popup_title' => __('Moving Otto Items'),
            'Not enough data' => __('Not enough data.'),
            'Product(s) was Unlinked.' => __('Product(s) was Unlinked.'),
            'Product(s) was Removed.' => __('Product(s) was Removed.'),
            'task_completed_message' => __('Task completed. Please wait ...'),
            'sending_data_message' => __('Sending %product_title% Product(s) data on Otto.'),
            'listing_locked_message' => __('The Listing was locked by another process. Please try again later.'),
            'listing_empty_message' => __('Listing is empty.'),

            'select_items_message' => __('Please select the Products you want to perform the Action on.'),
            'select_action_message' => __('Please select Action.'),
        ]);

        $this->js->addRequireJs(
            [
                'jQuery' => 'jquery',

                'p' => 'Otto/Plugin/ProgressBar',
                'a' => 'Otto/Plugin/AreaWrapper',
                'lm' => 'Otto/Listing/Moving',
                'lom' => 'Otto/Listing/Mapping',
                'loa' => 'Otto/Listing/Other/AutoMapping',
                'lor' => 'Otto/Listing/Other/Removing',
                'lou' => 'Otto/Listing/Other/Unmapping',

                'elog' => 'Otto/Otto/Listing/Other/Grid',
            ],
            <<<JS

        Otto.customData.gridId = 'ottoListingOtherGrid';

        window.OttoListingOtherGridObj = new OttoListingOtherGrid('ottoListingUnmanagedGrid');
        window.ListingOtherMappingObj = new ListingMapping(OttoListingOtherGridObj);

        OttoListingOtherGridObj.movingHandler.setProgressBar('listing_other_progress_bar');
        OttoListingOtherGridObj.movingHandler.setGridWrapper('listing_other_content_container');

        OttoListingOtherGridObj.autoMappingHandler.setProgressBar('listing_other_progress_bar');
        OttoListingOtherGridObj.autoMappingHandler.setGridWrapper('listing_other_content_container');

        jQuery(function() {
            OttoListingOtherGridObj.afterInitPage();
        });
JS
        );

        return '<div id="listing_other_progress_bar"></div>' .
            '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
            '<div id="listing_other_content_container">' .
            parent::_toHtml() .
            '</div>';
    }
}
