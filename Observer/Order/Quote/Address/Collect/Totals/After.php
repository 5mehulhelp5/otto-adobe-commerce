<?php

namespace M2E\Otto\Observer\Order\Quote\Address\Collect\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class After extends \M2E\Otto\Observer\AbstractObserver
{
    private PriceCurrencyInterface $priceCurrency;

    public function __construct(
        \M2E\Otto\Helper\Factory $helperFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($helperFactory);

        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return void
     */
    public function process(): void
    {
        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
        /** @var \Magento\Quote\Model\Quote $quote */
        $total = $this->getEvent()->getTotal();
        $quote = $this->getEvent()->getQuote();

        if ($quote->getIsOttoQuote() && $quote->getUseOttoDiscount()) {
            $discountAmount = $this->priceCurrency->convert($quote->getCoinDiscount());

            if ($total->getTotalAmount('subtotal')) {
                $total->setTotalAmount('subtotal', $total->getTotalAmount('subtotal') - $discountAmount);
            }

            if ($total->getBaseTotalAmount('subtotal')) {
                $total->setTotalAmount('subtotal', $total->getBaseTotalAmount('subtotal') - $discountAmount);
            }

            if ($total->hasData('grand_total') && $total->getGrandTotal()) {
                $total->setGrandTotal($total->getGrandTotal() - $discountAmount);
            }

            if ($total->hasData('base_grand_total') && $total->getBaseGrandTotal()) {
                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $discountAmount);
            }
        }
    }
}
