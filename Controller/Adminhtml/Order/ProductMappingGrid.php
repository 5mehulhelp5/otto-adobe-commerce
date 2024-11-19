<?php

namespace M2E\Otto\Controller\Adminhtml\Order;

use M2E\Otto\Controller\Adminhtml\AbstractOrder;

class ProductMappingGrid extends AbstractOrder
{
    public function execute()
    {
        $grid = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Order\Item\Product\Mapping\Grid::class);
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
