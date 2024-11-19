<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Moving;

class MoveToListingGrid extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Listing\Settings\MoveFromListing\Grid::class,
            '',
            [
                'ignoreListing' => (int)$this->getRequest()->getParam('ignoreListing'),
                'data' => [
                    'grid_url' => $this->getUrl(
                        '*/listing_moving/moveToListingGrid',
                        ['_current' => true]
                    ),
                ],
            ]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
