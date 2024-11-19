<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary\Attribute;

class Convertor
{
    /**
     * @param \M2E\Otto\Model\Dictionary\Attribute[] $attributes
     *
     * @return \M2E\Otto\Model\Category\Attribute\ProductAttribute[]
     */
    public function convert(array $attributes): array
    {
        $productAttributes = [];
        foreach ($attributes as $attribute) {
            $productAttributes[] = new \M2E\Otto\Model\Category\Attribute\ProductAttribute(
                $attribute->getId(),
                $attribute->getTitle(),
                $attribute->isRequired(),
                $attribute->isMultipleSelected(),
                $attribute->getDescription(),
                $attribute->getAllowedValues(),
                $attribute->getExampleValues()
            );
        }

        return $productAttributes;
    }
}
