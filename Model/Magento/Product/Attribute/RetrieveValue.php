<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Attribute;

class RetrieveValue
{
    private array $notFoundAttributeCodes = [];
    private string $errorMessage;
    private string $attributeTitle;

    private \M2E\Otto\Model\Magento\Product $magentoProduct;
    private \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;

    public function __construct(
        string $attributeTitle,
        \M2E\Otto\Model\Magento\Product $magentoProduct,
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper
    ) {
        $this->attributeTitle = $attributeTitle;
        $this->magentoProduct = $magentoProduct;
        $this->magentoAttributeHelper = $magentoAttributeHelper;
    }

    public function tryRetrieve(string $attributeCode): ?string
    {
        $this->magentoProduct->clearNotFoundAttributes();

        $result = $this->magentoProduct->getAttributeValue($attributeCode, true);
        if (!empty($result)) {
            return $result;
        }

        $notFoundAttributes = $this->magentoProduct->getNotFoundAttributes();
        if (!empty($notFoundAttributes)) {
            array_push($this->notFoundAttributeCodes, ...$notFoundAttributes);
        }

        return null;
    }

    // ----------------------------------------

    public function hasErrors(): bool
    {
        return !empty($this->notFoundAttributeCodes);
    }

    public function getErrorMessage(): string
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->errorMessage)) {
            return $this->errorMessage;
        }

        return $this->errorMessage = $this->compileErrorMessageByFoundAttributes();
    }

    private function compileErrorMessageByFoundAttributes(): string
    {
        $attributesTitles = [];

        $notFoundAttributesCodes = array_unique($this->notFoundAttributeCodes);
        foreach ($notFoundAttributesCodes as $attribute) {
            $attributesTitles[] = $this->magentoAttributeHelper->getAttributeLabel(
                $attribute,
                $this->magentoProduct->getStoreId()
            );
        }

        return (string)__(
            '%attribute_title: Attribute(s) %attributes were not found' .
            ' in this Product and its value was not sent.',
            [
                'attribute_title' => $this->attributeTitle,
                'attributes' => implode(', ', $attributesTitles),
            ]
        );
    }
}
