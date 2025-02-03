<?php

namespace M2E\Otto\Controller\Adminhtml\Product\Unmanaged\Moving;

class MoveToListingGrid extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Listing\Moving\Grid::class,
            '',
            [
                'accountId' => (int)$this->getRequest()->getParam('account_id'),
                'data' => [
                    'grid_url' => $this->getUrl(
                        '*/product_unmanaged_moving/MoveToListingGrid',
                        ['_current' => true]
                    ),
                ],
            ]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
