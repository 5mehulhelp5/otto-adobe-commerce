<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template;

class SellingFormatFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): SellingFormat
    {
        return $this->objectManager->create(SellingFormat::class);
    }
}
