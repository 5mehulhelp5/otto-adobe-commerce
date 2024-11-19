<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing;

class OtherFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Other
    {
        return $this->objectManager->create(Other::class);
    }
}
