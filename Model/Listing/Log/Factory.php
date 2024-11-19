<?php

namespace M2E\Otto\Model\Listing\Log;

use M2E\Otto\Model\Listing\Log;

class Factory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $data = []): Log
    {
        return $this->objectManager->create(Log::class, $data);
    }
}
