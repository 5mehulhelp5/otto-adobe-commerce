<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Attribute;

class RetrieveValue
{
    private array $errorMessages = [];

    private \M2E\Otto\Model\Magento\Product $magentoProduct;
    private \M2E\Otto\Helper\Magento\Attribute $magentoAttributeHelper;

    public function __construct(
        \M2E\Otto\Model\Magento\Product $magentoProduct,
        \M2E\Otto\Helper\Magento\Attribute $magentoAttributeHelper
    ) {
        $this->magentoProduct = $magentoProduct;
        $this->magentoAttributeHelper = $magentoAttributeHelper;
    }

    public function tryRetrieve(
        string $attributeCode,
        string $attributeTitle
    ): ?string {
        $this->errorMessages = [];
        $this->magentoProduct->clearNotFoundAttributes();

        // ----------------------------------------

        $result = $this->magentoProduct->getAttributeValue($attributeCode, true);
        if (!empty($result)) {
            return $result;
        }

        // ----------------------------------------

        $foundAttributes = $this->magentoProduct->getNotFoundAttributes();
        if (!empty($foundAttributes)) {
            $this->addNotFoundAttributesToErrors($attributeTitle, $foundAttributes);
        }

        // ----------------------------------------

        return null;
    }

    private function addNotFoundAttributesToErrors(
        string $title,
        array $attributes
    ): void {
        $attributesTitles = [];

        foreach ($attributes as $attribute) {
            $attributesTitles[] = $this->magentoAttributeHelper->getAttributeLabel(
                $attribute,
                $this->magentoProduct->getStoreId()
            );
        }

        $this->errorMessages[] = (string)__(
            '%1: Attribute(s) %2 were not found' .
            ' in this Product and its value was not sent.',
            (string)__($title),
            implode(',', $attributesTitles)
        );
    }

    // ----------------------------------------

    public function hasErrors(): bool
    {
        return !empty($this->errorMessages);
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }
}
