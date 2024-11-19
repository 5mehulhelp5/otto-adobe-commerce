<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Order\Shipment;

class TrackFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Otto\Model\Order $order, array $trackingDetails): Track
    {
        return $this->objectManager->create(Track::class, [
            'order' => $order,
            'trackingDetails' => $trackingDetails
        ]);
    }
}
