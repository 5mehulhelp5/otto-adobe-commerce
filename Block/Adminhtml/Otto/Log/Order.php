<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Log;

class Order extends \M2E\Otto\Block\Adminhtml\Log\Order\AbstractContainer
{
    protected function _toHtml()
    {
        if ($this->getRequest()->getParam('magento_order_failed')) {
            $message = <<<TEXT
This Log contains information about your recent Otto orders for which Magento orders were not created.<br/><br/>
Find detailed info in <a href="%url%" target="_blank">the article</a>.
TEXT;
        } else {
            $message = <<<TEXT
This Log contains information about Order processing.<br/><br/>
Find detailed info in <a href="%url" target="_blank">the article</a>.
TEXT;
        }
        $helpBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\HelpBlock::class)->setData([
            'content' => __(
                $message,
                ['url' => 'https://docs-m2.m2epro.com/m2e-otto-logs-events']
            ),
        ]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
