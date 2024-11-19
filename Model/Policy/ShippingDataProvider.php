<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Policy;

class ShippingDataProvider
{
    private \M2E\Otto\Model\Template\Shipping $shipping;

    public function __construct(
        \M2E\Otto\Model\Template\Shipping $shipping
    ) {
        $this->shipping = $shipping;
    }

    public function getHandlingTimeValue(): int
    {
        return $this->shipping->getHandlingTimeValue();
    }

    /** @deprecated */
    public function isHandlingTimeModeAttribute(): bool
    {
        return $this->shipping->isHandlingTimeModeAttribute();
    }

    /** @deprecated */
    public function getHandlingTimeAttribute(): string
    {
        return $this->shipping->getHandlingTimeAttribute();
    }

    public function getType(): string
    {
        return $this->shipping->getType();
    }

    public function getShippingProfileId(): ?string
    {
        return $this->shipping->getShippingProfileId();
    }
}
