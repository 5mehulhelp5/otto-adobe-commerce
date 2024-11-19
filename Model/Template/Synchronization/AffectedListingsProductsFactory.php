<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Synchronization;

class AffectedListingsProductsFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): AffectedListingsProducts
    {
        return $this->objectManager->create(AffectedListingsProducts::class);
    }
}
