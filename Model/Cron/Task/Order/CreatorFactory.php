<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order;

class CreatorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Synchronization\LogService $syncLogService
    ): Creator {
        return $this->objectManager->create(Creator::class, ['syncLogService' => $syncLogService]);
    }
}
