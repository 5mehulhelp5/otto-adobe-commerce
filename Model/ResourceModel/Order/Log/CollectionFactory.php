<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Order\Log;

use Magento\Framework\ObjectManagerInterface;

class CollectionFactory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private ObjectManagerInterface $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Collection
    {
        return $this->objectManager->create(Collection::class);
    }
}
