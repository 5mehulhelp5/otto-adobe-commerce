<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class ReserveFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Otto\Model\Order $order): Reserve
    {
        return $this->objectManager->create(Reserve::class, ['order' => $order]);
    }
}
