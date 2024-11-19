<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom;

class Stock extends AbstractModel
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
    public function getAttributeCode(): string
    {
        return 'is_in_stock';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return (string)__('Stock Availability');
    }

    /**
     * - MSI engine v. 2.3.2: Index tables have correct salable status
     * - Regular engine: Index table has status with no applied "Manage Stock" setting
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return int
     */
    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setProduct($product);

        return $this->magentoHelper->isMSISupportingVersion()
            ? (int)$magentoProduct->isStockAvailability()
            : (int)$magentoProduct->getStockItem()->getDataByKey('is_in_stock');
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getValueElementType(): string
    {
        return 'select';
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return [
            [
                'value' => 1,
                'label' => (string)__('In Stock'),
            ],
            [
                'value' => 0,
                'label' => (string)__('Out Of Stock'),
            ],
        ];
    }
}
