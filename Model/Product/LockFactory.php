<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

class LockFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(int $productId, string $initiator, \DateTime $createDate): Lock
    {
        $lock = $this->objectManager->create(Lock::class);

        $lock->init($productId, $initiator, $createDate);

        return $lock;
    }
}
