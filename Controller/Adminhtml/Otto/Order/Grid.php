<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Order;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractOrder;

class Grid extends AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\Otto\Block\Adminhtml\Otto\Order\Grid $grid */
        $grid = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Order\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
