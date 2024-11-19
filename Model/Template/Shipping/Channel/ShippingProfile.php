<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping\Channel;

class ShippingProfile
{
    private ?string $shippingProfileId;
    private string $shippingProfileName;
    private array $workingDays;
    private string $orderCutoff;
    private string $deliveryType;
    private int $defaultProcessingTime;
    private int $transportTime;

    public function __construct(
        ?string $shippingProfileId,
        string $shippingProfileName,
        array $workingDays,
        string $orderCutoff,
        string $deliveryType,
        int $defaultProcessingTime,
        int $transportTime
    ) {
        $this->shippingProfileName = $shippingProfileName;
        $this->workingDays = $workingDays;
        $this->orderCutoff = $orderCutoff;
        $this->deliveryType = $deliveryType;
        $this->defaultProcessingTime = $defaultProcessingTime;
        $this->transportTime = $transportTime;
        $this->shippingProfileId = $shippingProfileId;
    }

    public function setShippingProfileId(?string $shippingProfileId): void
    {
        $this->shippingProfileId = $shippingProfileId;
    }

    public function getShippingProfileId(): ?string
    {
        return $this->shippingProfileId;
    }

    public function setShippingProfileName(string $shippingProfileName): void
    {
        $this->shippingProfileName = $shippingProfileName;
    }

    public function getShippingProfileName(): string
    {
        return $this->shippingProfileName;
    }

    public function setWorkingDays(array $workingDays): void
    {
        $this->workingDays = $workingDays;
    }

    public function getWorkingDays(): array
    {
        return $this->workingDays;
    }

    public function setOrderCutoff(string $orderCutoff): void
    {
        $this->orderCutoff = $orderCutoff;
    }

    public function getOrderCutoff(): string
    {
        return $this->orderCutoff;
    }

    public function setDeliveryType(string $deliveryType): void
    {
        $this->deliveryType = $deliveryType;
    }

    public function getDeliveryType(): string
    {
        return $this->deliveryType;
    }

    public function setDefaultProcessingTime(int $defaultProcessingTime): void
    {
        $this->defaultProcessingTime = $defaultProcessingTime;
    }

    public function getDefaultProcessingTime(): int
    {
        return $this->defaultProcessingTime;
    }

    public function setTransportTime(int $transportTime): void
    {
        $this->transportTime = $transportTime;
    }

    public function getTransportTime(): int
    {
        return $this->transportTime;
    }
}
