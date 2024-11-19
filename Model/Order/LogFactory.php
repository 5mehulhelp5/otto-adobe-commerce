<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

use M2E\Otto\Model\Order\Log;

class LogFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $data = []): Log
    {
        return $this->objectManager->create(Log::class, $data);
    }
}
