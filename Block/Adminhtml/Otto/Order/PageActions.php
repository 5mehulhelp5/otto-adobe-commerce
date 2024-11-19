<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Order;

use M2E\Otto\Block\Adminhtml\Magento\AbstractBlock;

class PageActions extends AbstractBlock
{
    private const CONTROLLER_NAME = 'otto_order';

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml(): string
    {
        $accountSwitcherBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Account\Switcher::class)
            ->setData(['controller_name' => self::CONTROLLER_NAME]);

        $orderStateSwitcherBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Order\NotCreatedFilter::class)
            ->setData(['controller' => self::CONTROLLER_NAME]);

        return
            '<div class="filter_block">'
            . $accountSwitcherBlock->toHtml()
            . $orderStateSwitcherBlock->toHtml()
            . '</div>'
            . parent::_toHtml();
    }
}
