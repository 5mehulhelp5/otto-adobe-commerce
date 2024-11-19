<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Policy;

class ShippingDataProviderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    public function createShipping(
        \M2E\Otto\Model\Template\Shipping $shipping,
        \M2E\Otto\Model\Product $product
    ): ShippingDataProvider {
        return $this->objectManager->create(ShippingDataProvider::class, [
            'shipping' => $shipping,
            'product' => $product,
        ]);
    }
}
