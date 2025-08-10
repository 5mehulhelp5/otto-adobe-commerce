<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider;

use M2E\Otto\Model\Template\SellingFormat as SellingFormatPolicy;

class SalePriceProvider implements DataBuilderInterface
{
    use DataBuilderHelpTrait;

    public const NICK = 'SalePrice';

    private \M2E\Otto\Model\Currency $currency;
    private \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever;

    public function __construct(
        \M2E\Otto\Model\Currency $currency,
        \M2E\Otto\Model\Magento\Product\Attribute\RetrieveValueFactory $magentoAttributeRetriever
    ) {
        $this->currency = $currency;
        $this->magentoAttributeRetriever = $magentoAttributeRetriever;
    }

    public function getSalePrice(\M2E\Otto\Model\Product $product): ?SalePrice\Value
    {
        $sellingTemplate = $product->getSellingFormatTemplate();

        $salesPrice = $this->retrievePrice($product, $sellingTemplate);
        if (empty($salesPrice)) {
            return null;
        }

        $salePriceStartDate = $this->retrieveStartDate($product, $sellingTemplate);
        $salePriceEndDate = $this->retrieveEndDate($product, $sellingTemplate);
        if (
            $salePriceStartDate === null
            || $salePriceEndDate === null
        ) {
            $salePriceStartDate = null;
            $salePriceEndDate = null;
        }

        return new \M2E\Otto\Model\Product\DataProvider\SalePrice\Value(
            $salesPrice,
            $salePriceStartDate,
            $salePriceEndDate
        );
    }

    private function retrievePrice(
        \M2E\Otto\Model\Product $product,
        SellingFormatPolicy $sellingTemplate
    ): ?float {
        if ($sellingTemplate->getSalePriceMode() === SellingFormatPolicy::SALE_PRICE_MODE_NONE) {
            return null;
        }

        $attribute = $sellingTemplate->getSalePriceAttribute();
        if (empty($attribute)) {
            return null;
        }

        $value = (float)$this->retrieveMagentoAttributeValue($product, (string)__('Sales Price'), $attribute);
        if (empty($value)) {
            return null;
        }

        return round(
            (float)$this->currency->convertPrice(
                $value,
                \M2E\Otto\Model\Currency::CURRENCY_EUR,
                $product->getListing()->getStoreId()
            ),
            2
        );
    }

    private function retrieveStartDate(
        \M2E\Otto\Model\Product $product,
        SellingFormatPolicy $sellingTemplate
    ): ?\DateTime {
        if ($sellingTemplate->getSalePriceStartDateMode() === SellingFormatPolicy::SALE_PRICE_MODE_NONE) {
            return null;
        }

        $attribute = $sellingTemplate->getSalePriceStartDateAttribute();
        if (empty($attribute)) {
            return null;
        }
        $value = $this->retrieveMagentoAttributeValue(
            $product,
            (string)__('Sales Price Start Date'),
            $attribute
        );
        if (empty($value)) {
            return null;
        }

        $value = $this->tryCreateDateTime($value);
        if ($value === null) {
            $this->addWarningMessage((string)__('Sale price start date is not valid'));
        }

        return $value;
    }

    private function retrieveEndDate(
        \M2E\Otto\Model\Product $product,
        SellingFormatPolicy $sellingTemplate
    ): ?\DateTime {
        if ($sellingTemplate->getSalePriceEndDateMode() === SellingFormatPolicy::SALE_PRICE_MODE_NONE) {
            return null;
        }

        $attribute = $sellingTemplate->getSalePriceEndDateAttribute();
        if (empty($attribute)) {
            return null;
        }
        $value = $this->retrieveMagentoAttributeValue(
            $product,
            (string)__('Sales Price End Date'),
            $attribute
        );
        if (empty($value)) {
            return null;
        }

        $value = $this->tryCreateDateTime($value);
        if ($value === null) {
            $this->addWarningMessage((string)__('Sale price end date is not valid'));
        }

        return $value;
    }

    private function retrieveMagentoAttributeValue(
        \M2E\Otto\Model\Product $product,
        string $attributeTitle,
        string $attributeCode
    ): ?string {
        $attributeRetriever = $this->magentoAttributeRetriever->create(
            $attributeTitle,
            $product->getMagentoProduct()
        );
        $attributeVal = $attributeRetriever->tryRetrieve($attributeCode);

        if ($attributeVal === null) {
            $this->addNotFoundAttributesToWarning($attributeRetriever);

            return null;
        }

        return $attributeVal;
    }

    private function tryCreateDateTime(string $value): ?\DateTime
    {
        try {
            return \M2E\Core\Helper\Date::createDateGmt($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
