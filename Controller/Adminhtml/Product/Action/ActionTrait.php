<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Product\Action;

trait ActionTrait
{
    private function isRealtimeAction(array $products): bool
    {
        return count($products) <= 10;
    }

    private function redirectToGrid(): \Magento\Framework\App\ResponseInterface
    {
        return $this->_redirect('*/product_grid/allItems/');
    }
}
