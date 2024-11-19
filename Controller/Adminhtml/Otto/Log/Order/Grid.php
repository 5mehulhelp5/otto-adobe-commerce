<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Log\Order;

class Grid extends \M2E\Otto\Controller\Adminhtml\Otto\Log\AbstractOrder
{
    public function execute()
    {
        $response = $this->getLayout()
                         ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Log\Order\Grid::class)
                         ->toHtml();
        $this->setAjaxContent($response);

        return $this->getResult();
    }
}
