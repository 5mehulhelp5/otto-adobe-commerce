<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\UpdateFromChannel;

class ProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Listing\Other\OttoProduct $channelProduct
    ): Processor {
        return $this->objectManager->create(
            Processor::class,
            [
                'product' => $product,
                'channelProduct' => $channelProduct,
            ],
        );
    }
}
