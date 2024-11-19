<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

use M2E\Otto\Model\Category\Attribute;

class BrandProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'Brand';

    private \M2E\Otto\Model\Brand\Resolver $brandResolver;

    private \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever;

    public function __construct(
        \M2E\Otto\Model\Brand\Resolver $brandResolver,
        \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever
    ) {
        $this->brandResolver = $brandResolver;
        $this->magentoAttributeRetriever = $magentoAttributeRetriever;
    }

    public function getBrand(\M2E\Otto\Model\Product $product): ?Brand\Value
    {
        $category = $product->getCategoryTemplate();

        $attribute = $category->getBrandAttribute();

        if ($attribute === null) {
            return null;
        }

        $brandName = $this->findBrandName($attribute, $product);
        if ($brandName === null) {
            return null;
        }

        $brand = $this->brandResolver->resolveByBrandName($product->getAccount(), $brandName);
        if ($brand === null) {
            $this->addWarningMessage((string)__('Brand is not valid'));

            return null;
        }

        return new Brand\Value($brandName, $brand->getBrandId());
    }

    private function findBrandName(\M2E\Otto\Model\Category\Attribute $attribute, \M2E\Otto\Model\Product $product): ?string
    {
        if ($attribute->isValueModeNone()) {
            return null;
        }

        if ($attribute->isValueModeCustomAttribute()) {
            $attributeRetriever = $this->magentoAttributeRetriever->create($product->getMagentoProduct());
            $attributeVal = $attributeRetriever->tryRetrieve($attribute->getCustomAttributeValue(), 'Brand');

            if ($attributeVal === null) {
                $this->addNotFoundAttributesToWarning($attributeRetriever);

                return null;
            }

            return $attributeVal;
        }

        if ($attribute->isValueModeCustomValue()) {
            $attributeVal = $attribute->getCustomValue();

            if (empty($attributeVal)) {
                $this->addWarningMessage((string)__('Brand is missing a value.'));

                return null;
            }

            return $attributeVal;
        }

        return null;
    }
}
