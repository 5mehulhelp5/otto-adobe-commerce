<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template;

class ShippingFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Shipping
    {
        return $this->objectManager->create(Shipping::class);
    }

    public function create(
        \M2E\Otto\Model\Account $account,
        string $title,
        int $handlingTime,
        int $transportTime,
        string $orderCutoff,
        array $workingDays,
        string $type,
        string $shippingProfileId
    ): Shipping {
        $model = $this->createEmpty();
        $model->create(
            $account->getId(),
            $title,
            $handlingTime,
            $transportTime,
            $orderCutoff,
            $workingDays,
            $type,
            $shippingProfileId,
        );

        return $model;
    }
}
