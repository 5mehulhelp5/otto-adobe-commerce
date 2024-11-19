<?php

namespace M2E\Otto\Model\Template\Description;

use M2E\Otto\Model\Template\Description\SnapshotBuilder;

class SnapshotBuilderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): SnapshotBuilder
    {
        return $this->objectManager->create(SnapshotBuilder::class);
    }
}
