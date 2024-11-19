<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Shipment;

class TrackingDetails
{
    private string $carrierCode;
    private string $carrierTitle;
    private string $shippingMethod;
    private string $trackingNumber;

    public function __construct(
        string $carrierCode,
        string $carrierTitle,
        string $shippingMethod,
        string $trackingNumber
    ) {
        $this->carrierCode = $carrierCode;
        $this->carrierTitle = $carrierTitle;
        $this->shippingMethod = $shippingMethod;
        $this->trackingNumber = $trackingNumber;
    }

    public function getCarrierCode(): string
    {
        return $this->carrierCode;
    }

    public function getCarrierTitle(): string
    {
        return $this->carrierTitle;
    }

    public function getShippingMethod(): string
    {
        return $this->shippingMethod;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }
}
