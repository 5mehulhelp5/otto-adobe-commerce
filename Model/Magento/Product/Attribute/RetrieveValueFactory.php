<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Attribute;

class RetrieveValueFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Magento\Product $magentoProduct
    ): RetrieveValue {
        return $this->objectManager->create(
            RetrieveValue::class,
            ['magentoProduct' => $magentoProduct]
        );
    }
}
