<?php

namespace M2E\Otto\Model\Magento;

/**
 * Class \M2E\Otto\Model\Magento\Payment
 */
class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'ottopayment';

    protected $_canUseCheckout = false;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    protected $_infoBlockType = \M2E\Otto\Block\Adminhtml\Magento\Payment\Info::class;

    //########################################

    public function isAvailable(?\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $data = $data->getData()['additional_data'];

        $details = [
            'payment_method' => $data['payment_method'],
            'channel_order_number' => $data['channel_order_number'],
            'cash_on_delivery_cost' => $data['cash_on_delivery_cost'] ?? null,
            'transactions' => $data['transactions'],
            'tax_id' => isset($data['tax_id']) ? $data['tax_id'] : null,
        ];

        $this->getInfoInstance()->setAdditionalInformation($details);

        return $this;
    }

    //########################################
}
