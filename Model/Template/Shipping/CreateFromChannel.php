<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping;

class CreateFromChannel
{
    private Repository $repository;
    private \M2E\Otto\Model\Template\ShippingFactory $shippingFactory;

    public function __construct(
        Repository $repository,
        \M2E\Otto\Model\Template\ShippingFactory $shippingFactory
    ) {
        $this->shippingFactory = $shippingFactory;
        $this->repository = $repository;
    }

    public function process(
        \M2E\Otto\Model\Account $account,
        \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile $channel
    ): \M2E\Otto\Model\Template\Shipping {
        $shipping = $this->shippingFactory->create(
            $account,
            $channel->getShippingProfileName(),
            $channel->getDefaultProcessingTime(),
            $channel->getTransportTime(),
            $channel->getOrderCutoff(),
            $channel->getWorkingDays(),
            $channel->getDeliveryType(),
            $channel->getShippingProfileId(),
        );

        $this->repository->create($shipping);

        return $shipping;
    }
}
