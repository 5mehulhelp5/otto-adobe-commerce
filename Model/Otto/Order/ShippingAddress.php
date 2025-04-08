<?php

namespace M2E\Otto\Model\Otto\Order;

class ShippingAddress extends \M2E\Otto\Model\Order\ShippingAddress
{
    /**
     * @return array
     */
    public function getRawData(): array
    {
        $buyerName = $this->order->getBuyerName();
        $recipientName = $this->getData('recipient_name');

        return [
            'buyer_name' => $buyerName,
            'email' => $this->getBuyerEmail(),

            'recipient_name' => $recipientName ?: $buyerName,
            'postcode' => $this->getPostalCode(),
            'country_id' => $this->getData('country_code'),
            'city' => $this->getData('city') ? $this->getData('city') : $this->getCountryName(),
            'street' => [$this->getStreet(), $this->order->getShippingAdditionalInfo()],
            'telephone' => $this->getPhone(),
            'company' => $this->getData('company'),

            'billing_name' => $this->getData('billing_name'),
            'billing_postal_code' => $this->getData('billing_postal_code') ?: '0000',
            'billing_country_id' => $this->getData('billing_country_code'),
            'billing_city' => $this->getData('billing_city'),
            'billing_street' => $this->getData('billing_street'),
            'billing_telephone' => $this->getData('billing_phone') ?: '0000000000',
            'billing_company' => null,
        ];
    }

    protected function getBuyerEmail()
    {
        return $this->order->getBuyerEmail();
    }

    protected function getPostalCode()
    {
        $postalCode = $this->getData('postal_code');

        if (stripos($postalCode, 'Invalid Request') !== false || $postalCode == '') {
            $postalCode = '0000';
        }

        return $postalCode;
    }

    protected function getPhone()
    {
        $phone = $this->getData('phone');

        if (stripos($phone, 'Invalid Request') !== false || $phone == '') {
            $phone = '0000000000';
        }

        return $phone;
    }

    protected function getStreet()
    {
        return $this->getData('street');
    }

    protected function isRegionOverrideRequired(): bool
    {
        return $this->order->getAccount()->getOrdersSettings()->isRegionOverrideRequired();
    }
}
