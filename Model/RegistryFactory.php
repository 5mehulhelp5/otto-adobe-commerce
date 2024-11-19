<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

class RegistryFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Registry
    {
        return $this->objectManager->create(Registry::class);
    }
}
