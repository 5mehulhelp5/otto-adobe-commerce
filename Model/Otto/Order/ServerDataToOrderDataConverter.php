<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Order;

class ServerDataToOrderDataConverter
{
    private \M2E\Otto\Model\Otto\Order\StatusResolver $orderStatusResolver;
    private \Magento\Directory\Model\CountryFactory $countryFactory;

    public function __construct(
        \M2E\Otto\Model\Otto\Order\StatusResolver $orderStatusResolver,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->orderStatusResolver = $orderStatusResolver;
        $this->countryFactory = $countryFactory;
    }

    public function convert(array $data): array
    {
        return [
            'otto_order_id' => $data['order_id'],
            'otto_order_number' => $data['order_number'],
            'order_status' => $this->orderStatusResolver->resolveByOrderItems($data['order_items']),
            'purchase_update_date' => $data['update_date'],
            'purchase_create_date' => $data['create_date'],
            'currency' => \M2E\Otto\Model\Currency::CURRENCY_EUR,
            'buyer_name' => $this->getBuyerName($data),
            'buyer_email' => $this->getEmail($data),
            'payment_method_name' => $data['payment_method'],
            'shipping_date_to' => $this->getShippingDateTo($data),
            'paid_amount' => $this->calculateTotalPaidAmount($data),
            'tax_details' => json_encode($this->getTaxDetails($data), JSON_THROW_ON_ERROR),
            'shipping_details' => json_encode($this->getShippingDetails($data), JSON_THROW_ON_ERROR),
        ];
    }

    private function calculateShippingFeeAmount(array $data): float
    {
        $shippingFeesAmount = 0.0;
        foreach ($data['initial_delivery_fees'] as $deliveryFee) {
            $shippingFeesAmount += (float)$deliveryFee['fee']['amount'];
        }

        return $shippingFeesAmount;
    }

    private function calculateProductTaxAmount(array $data): float
    {
        $productTaxAmount = 0.0;
        foreach ($data['order_items'] as $item) {
            $price = $item['gross_reduced_price']['amount'] ?? $item['gross_price']['amount'];
            $taxRate = $item['product']['vat_rate'] / 100;
            $tax = $price * (1 - 1 / (1 + $taxRate));
            $productTaxAmount += $tax;
        }

        return $productTaxAmount;
    }

    private function calculateTotalPaidAmount(array $data): float
    {
        $subtotal = 0.0;
        foreach ($data['order_items'] as $item) {
            $subtotal += $item['gross_reduced_price']['amount'] ?? $item['gross_price']['amount'];
        }

        return $subtotal + $this->calculateShippingFeeAmount($data);
    }

    private function getBuyerName(array $data): string
    {
        return trim($data['invoice_address']['first_name'] . ' ' . $data['invoice_address']['last_name']);
    }

    private function getEmail(array $data): string
    {
        if (!empty($data['delivery_address']['email'])) {
            return $data['delivery_address']['email'];
        }

        if (!empty($data['invoice_address']['email'])) {
            return $data['invoice_address']['email'];
        }

        return (new FakeEmailGenerator())
            ->generate($data['invoice_address']['first_name'], $data['invoice_address']['last_name']);
    }

    private function getShippingDateTo(array $data): ?string
    {
        $closestDeliveryDate = null;
        foreach ($data['order_items'] as $item) {
            $deliveryDate = $item['expected_delivery_date'];

            if ($deliveryDate !== null) {
                $deliveryDateTime = \M2E\Core\Helper\Date::createDateGmt($deliveryDate);

                if ($closestDeliveryDate === null || $deliveryDateTime < $closestDeliveryDate) {
                    $closestDeliveryDate = $deliveryDateTime;
                }
            }
        }

        if ($closestDeliveryDate === null) {
            return null;
        }

        return $closestDeliveryDate->format('Y-m-d H:i:s');
    }

    private function getShippingAddress(array $data): array
    {
        $invoiceAddress = $data['invoice_address'];
        $deliveryAddress = $data['delivery_address'];

        $billingName = trim($invoiceAddress['first_name'] . ' ' . $invoiceAddress['last_name']);

        return [
            'buyer_name' => $billingName,
            'recipient_name' => trim($deliveryAddress['first_name'] . ' ' . $deliveryAddress['last_name']),
            'postal_code' => $deliveryAddress['zip_code'] ?? '',
            'country_code' => $this->getCountryCodeByIso3166Code($deliveryAddress['country_code']),
            'city' => $deliveryAddress['city'],
            'street' => trim($deliveryAddress['street'] . ', ' . $deliveryAddress['house_number'], ' ,'),
            'phone' => $deliveryAddress['phone_number'] ?? '',

            'billing_name' => $billingName,
            'billing_postal_code' => $invoiceAddress['zip_code'] ?? '',
            'billing_country_code' => $this->getCountryCodeByIso3166Code($invoiceAddress['country_code']),
            'billing_city' => $invoiceAddress['city'],
            'billing_street' => trim($invoiceAddress['street'] . ', ' . $invoiceAddress['house_number'], ' ,'),
            'billing_phone' => $invoiceAddress['phone_number'] ?? '',
        ];
    }

    private function getCountryCodeByIso3166Code($isoCountryCode)
    {
        return $this->countryFactory->create()->loadByCode($isoCountryCode)->getId();
    }

    private function getTaxDetails(array $data): array
    {
        $shippingFeesAmount = $this->calculateShippingFeeAmount($data);
        $rate = (float)($data['initial_delivery_fees']['vat_rate'] ?? 0.0);
        $shippingTaxAmount = $shippingFeesAmount * (1 - 1 / (1 + $rate / 100));

        $productTaxAmount = $this->calculateProductTaxAmount($data);
        $totalTaxAmount = $productTaxAmount + $shippingTaxAmount;

        return [
            'product_amount' => $productTaxAmount,
            'shipping_amount' => $shippingTaxAmount,
            'total_amount' => $totalTaxAmount,
            'rate' => $rate,
            'is_vat' => false,
        ];
    }

    private function getShippingDetails(array $data): array
    {
        return [
            'price' => $this->calculateShippingFeeAmount($data),
            'services' => array_column($data['initial_delivery_fees'], 'name'),
            'address' => $this->getShippingAddress($data),
            'additional_info' => $this->getShippingAdditionalInfo($data),
        ];
    }

    private function getShippingAdditionalInfo(array $data): string
    {
        $deliveryAddition = $data['delivery_address']['addition'] ?? '';
        $invoiceAddition = $data['invoice_address']['addition'] ?? '';

        if ($deliveryAddition === $invoiceAddition) {
            return $deliveryAddition;
        }

        if (!empty($deliveryAddition) && !empty($invoiceAddition)) {
            return $deliveryAddition . ' ' . $invoiceAddition;
        }

        return empty($deliveryAddition) ? $invoiceAddition : $deliveryAddition;
    }
}
