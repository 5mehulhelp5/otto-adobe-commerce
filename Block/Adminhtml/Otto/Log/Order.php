<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Log;

class Order extends \M2E\Otto\Block\Adminhtml\Log\Order\AbstractContainer
{
    protected function _toHtml()
    {
        $url = 'https://docs-m2.m2epro.com/m2e-otto-logs-events';
        if ($this->getRequest()->getParam('magento_order_failed')) {
            $message = __(
                'This Log contains information about your recent %channel_title orders for which Magento orders were not created.<br/><br/>
                Find detailed info in <a href="%url%" target="_blank">the article</a>.',
                [
                    'url' => $url,
                    'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                ]
            );
        } else {
            $message = __(
                'This Log contains information about Order processing.<br/><br/>
                Find detailed info in <a href="%url" target="_blank">the article</a>.',
                ['url' => $url]
            );
        }
        $helpBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\HelpBlock::class)->setData([
            'content' => $message,
        ]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
