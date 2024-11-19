<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Order;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractOrder;

class Index extends AbstractOrder
{
    public function execute()
    {
        $this->init();
        $this->addContent($this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Order::class));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-otto-orders');

        return $this->getResultPage();
    }
}
