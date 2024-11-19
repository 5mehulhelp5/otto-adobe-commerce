<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

class DetailsProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Details';

    private \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever;

    public function __construct(
        \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever
    ) {
        $this->magentoAttributeRetriever = $magentoAttributeRetriever;
    }

    public function getDetails(\M2E\Otto\Model\Product $product): \M2E\Otto\Model\Product\DataProvider\Details\Value
    {
        $bulletPoints = $product->getDescriptionTemplateSource()->getBulletPoints();

        $category = $product->getCategoryTemplate();

        $manufacturer = $this->getManufacturerAttributeData($product, $category);
        $mpn = $this->getMpnAttributeData($product, $category);

        return new \M2E\Otto\Model\Product\DataProvider\Details\Value($bulletPoints, $manufacturer, $mpn);
    }

    private function getManufacturerAttributeData(\M2E\Otto\Model\Product $product, \M2E\Otto\Model\Category $category): ?string
    {
        $attribute = $category->getManufactureAttribute();

        if ($attribute === null) {
            return null;
        }

        if ($attribute->isValueModeNone()) {
            return null;
        }

        if ($attribute->isValueModeCustomAttribute()) {
            $attributeRetriever = $this->magentoAttributeRetriever->create($product->getMagentoProduct());
            $attributeVal = $attributeRetriever->tryRetrieve($attribute->getCustomAttributeValue(), 'Manufacturer');

            if ($attributeVal !== null) {
                return $attributeVal;
            }

            $this->addNotFoundAttributesToWarning($attributeRetriever);

            return null;
        }

        if ($attribute->isValueModeCustomValue()) {
            $manufacturerAttributeVal = $attribute->getCustomValue();

            if (!empty($manufacturerAttributeVal)) {
                return $manufacturerAttributeVal;
            }
        }

        return null;
    }

    private function getMpnAttributeData(\M2E\Otto\Model\Product $product, \M2E\Otto\Model\Category $category): ?string
    {
        $attribute = $category->getMpnAttribute();
        if ($attribute === null) {
            return null;
        }

        if ($attribute->isValueModeNone()) {
            return null;
        }

        if ($attribute->isValueModeCustomAttribute()) {
            $attributeRetriever = $this->magentoAttributeRetriever->create($product->getMagentoProduct());
            $attributeVal = $attributeRetriever->tryRetrieve($attribute->getCustomAttributeValue(), 'MPN');

            if ($attributeVal !== null) {
                return $attributeVal;
            }

            $this->addNotFoundAttributesToWarning($attributeRetriever);

            return null;
        }

        if ($attribute->isValueModeCustomValue()) {
            $mpnAttributeVal = $attribute->getCustomValue();

            if (!empty($mpnAttributeVal)) {
                return $mpnAttributeVal;
            }
        }

        return null;
    }
}
