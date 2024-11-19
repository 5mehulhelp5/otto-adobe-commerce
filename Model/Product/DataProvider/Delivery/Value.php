<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\Delivery;

class Value
{
    public ?string $shippingProfileId;
    public string $deliveryType;
    public int $deliveryTime;

    public function __construct(
        ?string $shippingProfileId,
        string $deliveryType,
        int $deliveryTime
    ) {
        $this->shippingProfileId = $shippingProfileId;
        $this->deliveryType = $deliveryType;
        $this->deliveryTime = $deliveryTime;
    }
}
