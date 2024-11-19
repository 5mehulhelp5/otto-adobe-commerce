<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing;

class GetErrorsSummary extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    private \M2E\Otto\Model\ResourceModel\Listing\Log $listingLogResource;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Listing\Log $listingLogResource,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingLogResource = $listingLogResource;
    }

    public function execute()
    {
        $blockParams = [
            'action_ids' => $this->getRequest()->getParam('action_ids'),
            'table_name' => $this->listingLogResource->getMainTable(),
            'type_log' => 'listing',
        ];
        $block = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Listing\Log\ErrorsSummary::class,
            '',
            ['data' => $blockParams]
        );
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
