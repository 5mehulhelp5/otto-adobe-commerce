<?php

namespace M2E\Otto\Model\Magento\Product;

class ImageFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Image
    {
        return $this->objectManager->create(Image::class);
    }
}
