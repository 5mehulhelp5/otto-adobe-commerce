<?php

namespace M2E\Otto\Model\Order\Item;

class ProxyObjectFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Order\Item $orderItem,
        array $data = []
    ): ProxyObject {
        $data['item'] = $orderItem;

        return $this->objectManager->create(ProxyObject::class, $data);
    }
}
