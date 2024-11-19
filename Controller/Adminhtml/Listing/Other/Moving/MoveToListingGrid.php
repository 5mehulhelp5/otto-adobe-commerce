<?php

namespace M2E\Otto\Controller\Adminhtml\Listing\Other\Moving;

class MoveToListingGrid extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    private \M2E\Otto\Helper\Data\GlobalData $globalData;

    public function __construct(
        \M2E\Otto\Helper\Data\GlobalData $globalData,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->globalData = $globalData;
    }

    public function execute()
    {
        $this->globalData->setValue(
            'accountId',
            $this->getRequest()->getParam('accountId')
        );

        $this->globalData->setValue(
            'ignoreListings',
            \M2E\Otto\Helper\Json::decode($this->getRequest()->getParam('ignoreListings'))
        );

        $block = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Listing\Moving\Grid::class,
            '',
            [
                'data' => [
                    'grid_url' => $this->getUrl(
                        '*/listing_other_moving/moveToListingGrid',
                        ['_current' => true]
                    ),
                    'moving_handler_js' => 'OttoListingOtherGridObj.movingHandler',
                ],
            ]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
