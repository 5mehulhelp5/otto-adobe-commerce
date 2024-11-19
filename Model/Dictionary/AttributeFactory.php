<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary;

class AttributeFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): \M2E\Otto\Model\Dictionary\Attribute
    {
        return $this->objectManager->create(\M2E\Otto\Model\Dictionary\Attribute::class);
    }
}
