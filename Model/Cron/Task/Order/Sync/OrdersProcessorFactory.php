<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order\Sync;

use M2E\Otto\Model\Cron\Task\Order\Sync\OrdersProcessor;

class OrdersProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Account $account,
        \M2E\Otto\Model\Synchronization\LogService $logService
    ): OrdersProcessor {
        return $this->objectManager->create(
            OrdersProcessor::class,
            ['logService' => $logService, 'account' => $account],
        );
    }
}
