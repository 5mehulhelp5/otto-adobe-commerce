<?php

namespace M2E\Otto\Model\Magento\Product\Inventory;

use M2E\Otto\Model\Magento\Product\Inventory;
use M2E\Otto\Model\MSI\Magento\Product\Inventory as MSIInventory;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

class Factory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\Otto\Helper\Magento $magentoHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Otto\Helper\Magento $magentoHelper
    ) {
        $this->objectManager = $objectManager;
        $this->magentoHelper = $magentoHelper;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \M2E\Otto\Model\AbstractModel|AbstractModel
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getObject(\Magento\Catalog\Model\Product $product)
    {
        $object = $this->objectManager->get($this->isMsiMode($product) ? MSIInventory::class : Inventory::class);
        $object->setProduct($product);

        return $object;
    }

    private function isMsiMode(\Magento\Catalog\Model\Product $product): bool
    {
        if (!$this->magentoHelper->isMSISupportingVersion()) {
            return false;
        }

        if (interface_exists(IsSourceItemManagementAllowedForProductTypeInterface::class)) {
            $isSourceItemManagementAllowedForProductType = $this->objectManager->get(
                IsSourceItemManagementAllowedForProductTypeInterface::class
            );

            return $isSourceItemManagementAllowedForProductType->execute($product->getTypeId());
        }

        return true;
    }
}
