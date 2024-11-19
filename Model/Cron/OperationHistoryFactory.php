<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron;

class OperationHistoryFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): OperationHistory
    {
        /** @var OperationHistory */
        return $this->objectManager->create(OperationHistory::class);
    }
}
