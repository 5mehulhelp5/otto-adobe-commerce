<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom;

class Qty extends AbstractModel
{
    private \M2E\Otto\Model\Magento\ProductFactory $magentoProductFactory;
    private \M2E\Otto\Helper\Magento $magentoHelper;

    public function __construct(
        \M2E\Otto\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\Otto\Helper\Magento $magentoHelper
    ) {
        $this->magentoProductFactory = $magentoProductFactory;
        $this->magentoHelper = $magentoHelper;
    }

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'qty';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return (string)__('QTY');
    }

    /**
     * - MSI engine v. 2.3.2: Index tables have correct QTY for all product except Bundle
     * - Regular engine: Index table has 0 QTY for complex products (bundle, configurable, grouped)
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return float
     * @throws \M2E\Otto\Model\Exception
     */
    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setProduct($product);

        if ($this->magentoHelper->isMSISupportingVersion()) {
            return $magentoProduct->isBundleType() ? 0 : $magentoProduct->getQty();
        }

        if (
            $magentoProduct->isBundleType() ||
            $magentoProduct->isConfigurableType() ||
            $magentoProduct->isGroupedType()
        ) {
            return 0;
        }

        return $magentoProduct->getQty();
    }
}
