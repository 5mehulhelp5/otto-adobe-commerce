<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\InventorySync\Processing;

class InitiatorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Account $account
    ): Initiator {
        return $this->objectManager->create(Initiator::class, ['account' => $account]);
    }
}
