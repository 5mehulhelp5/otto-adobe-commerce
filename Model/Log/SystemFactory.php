<?php

namespace M2E\Otto\Model\Log;

class SystemFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $data = []): System
    {
        return $this->objectManager->create(System::class, $data);
    }
}
