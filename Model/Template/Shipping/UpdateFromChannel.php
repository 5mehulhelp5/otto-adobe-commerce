<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping;

class UpdateFromChannel
{
    private Repository $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    public function process(
        \M2E\Otto\Model\Template\Shipping $shipping,
        \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile $channel
    ): void {
        if ($this->hasChanges($shipping, $channel)) {
            $shipping->setTitle($channel->getShippingProfileName())
                      ->setHandlingTimeValue($channel->getDefaultProcessingTime())
                      ->setTransportTime($channel->getTransportTime())
                      ->setOrderCutoff($channel->getOrderCutoff())
                      ->setWorkingDays($channel->getWorkingDays())
                      ->setType($channel->getDeliveryType());

            $this->repository->save($shipping);
        }
    }

    private function hasChanges(\M2E\Otto\Model\Template\Shipping $shipping, Channel\ShippingProfile $channel): bool
    {
        return $shipping->getTitle() !== $channel->getShippingProfileName()
            || $shipping->getWorkingDays() !== $channel->getWorkingDays()
            || $shipping->getOrderCutoff() !== $channel->getOrderCutoff()
            || $shipping->getType() !== $channel->getDeliveryType()
            || $shipping->getHandlingTimeValue() !== $channel->getDefaultProcessingTime()
            || $shipping->getTransportTime() !== $channel->getTransportTime();
    }
}
