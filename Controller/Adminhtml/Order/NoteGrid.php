<?php

namespace M2E\Otto\Controller\Adminhtml\Order;

use M2E\Otto\Controller\Adminhtml\AbstractOrder;

class NoteGrid extends AbstractOrder
{
    public function execute()
    {
        $grid = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Order\Note\Grid::class);
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
