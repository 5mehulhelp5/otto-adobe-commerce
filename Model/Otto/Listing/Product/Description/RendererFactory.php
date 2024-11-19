<?php

namespace M2E\Otto\Model\Otto\Listing\Product\Description;

use M2E\Otto\Model\Otto\Listing\Product\Description\Renderer;

class RendererFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Otto\Model\Product $listingProduct): Renderer
    {
        return $this->objectManager->create(Renderer::class, [
            'listingProduct' => $listingProduct,
        ]);
    }
}
