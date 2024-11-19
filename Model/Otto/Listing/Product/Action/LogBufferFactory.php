<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

class LogBufferFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): \M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer
    {
        return $this->objectManager->create(\M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer::class);
    }
}
