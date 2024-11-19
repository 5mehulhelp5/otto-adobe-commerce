<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto;

abstract class AbstractOrder extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Otto::sales_orders');
    }

    protected function init()
    {
        $this->addCss('order.css');
        $this->addCss('switcher.css');
        $this->addCss('otto/order/grid.css');

        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Sales'));
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Orders'));
    }
}
