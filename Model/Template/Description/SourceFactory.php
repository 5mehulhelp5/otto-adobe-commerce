<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Description;

use M2E\Otto\Model\Template\Description\Source;

class SourceFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Source
    {
        return $this->objectManager->create(Source::class);
    }
}
