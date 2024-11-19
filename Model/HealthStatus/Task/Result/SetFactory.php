<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus\Task\Result;

class SetFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Set
    {
        return $this->objectManager->create(Set::class);
    }
}
