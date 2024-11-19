<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom;

class StockFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Stock
    {
        return $this->objectManager->create(Stock::class);
    }
}
