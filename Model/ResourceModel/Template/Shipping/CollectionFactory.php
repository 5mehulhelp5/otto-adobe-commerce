<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Template\Shipping;

use M2E\Otto\Model\ResourceModel\Template\Shipping\Collection as TemplateShippingCollection;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): TemplateShippingCollection
    {
        return $this->objectManager->create(TemplateShippingCollection::class);
    }
}
