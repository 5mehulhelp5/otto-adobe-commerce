<?php

namespace M2E\Otto\Model\Template;

use M2E\Otto\Model\Template\Description;

class DescriptionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Description
    {
        return $this->objectManager->create(Description::class);
    }
}
