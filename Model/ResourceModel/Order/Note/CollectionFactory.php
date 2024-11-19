<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Order\Note;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $data = []): Collection
    {
        return $this->objectManager->create(Collection::class, $data);
    }
}
