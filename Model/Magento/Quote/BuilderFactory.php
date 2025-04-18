<?php

namespace M2E\Otto\Model\Magento\Quote;

class BuilderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Order\ProxyObject $proxyOrder,
        array $data = []
    ): Builder {
        $data['proxyOrder'] = $proxyOrder;

        return $this->objectManager->create(Builder::class, $data);
    }
}
