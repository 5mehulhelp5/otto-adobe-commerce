<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Item;

class ProxyObjectFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Order\Item $orderItem
    ): ProxyObject {
        return $this->objectManager->create(ProxyObject::class, ['item' => $orderItem]);
    }
}
