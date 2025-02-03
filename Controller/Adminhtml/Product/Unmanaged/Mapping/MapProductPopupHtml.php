<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Product\Unmanaged\Mapping;

class MapProductPopupHtml extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $productOtherId = $this->getRequest()->getParam('other_product_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $block = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Listing\Mapping\View::class,
            '',
            [
                'data' => [
                    'grid_url' => '*/product_unmanaged_mapping/mapGrid',
                    'other_product_id' => $productOtherId,
                    'account_id' => $accountId,
                ],
            ]
        );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
