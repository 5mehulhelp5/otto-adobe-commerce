<?php

namespace M2E\Otto\Model\Order\Log;

use M2E\Otto\Model\Order\Log\Service;

class ServiceFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Service
    {
        return $this->objectManager->create(Service::class);
    }
}
