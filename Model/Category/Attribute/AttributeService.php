<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Category\Attribute;

use M2E\Otto\Model\Category\Attribute;

class AttributeService
{
    private \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository;
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\Otto\Model\Dictionary\Attribute\Convertor $attributeConvertor;

    public function __construct(
        \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository,
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\Otto\Model\Dictionary\Attribute\Convertor $attributeConvertor
    ) {
        $this->attributeConvertor = $attributeConvertor;
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
        $this->attributeDictionaryRepository = $attributeDictionaryRepository;
    }

    public function getProductAttributes(
        string $categoryGroupId,
        int $categoryId = null
    ): array {

        $savedAttributes = [];
        $attributes = [];

        $dictionaryAttributes = $this->attributeDictionaryRepository->getAttributesByCategoryGroupId($categoryGroupId);
        $dictionaryAttributes = $this->attributeConvertor->convert($dictionaryAttributes);
        if ($categoryId) {
            $category = $this->categoryRepository->get($categoryId);
            $savedAttributes = $this->loadSavedAttributes($category, [
                Attribute::ATTRIBUTE_TYPE_PRODUCT
            ]);
        }

        if (empty($dictionaryAttributes) && !empty($savedAttributes)) {
            foreach ($savedAttributes as $savedAttribute) {
                $item = $this->convertSavedAttributesToAttributesArray($savedAttribute);
                $attributes[] = $item;
            }
        } else {
            foreach ($dictionaryAttributes as $productAttribute) {
                $item = $this->map($productAttribute, $savedAttributes);

                if ($item['required']) {
                    array_unshift($attributes, $item);
                    continue;
                }

                $attributes[] = $item;
            }
        }

        return $this->sortAttributesByTitle($attributes);
    }

    public function getCustomAttributes(?int $categoryId): array
    {
        $savedAttributes = [];

        if ($categoryId) {
            $category = $this->categoryRepository->get($categoryId);
            $savedAttributes = $this->loadSavedAttributes($category, [
                Attribute::ATTRIBUTE_TYPE_BRAND,
                Attribute::ATTRIBUTE_TYPE_MPN,
                Attribute::ATTRIBUTE_TYPE_MANUFACTURER
            ]);
        }

        $attributes = [];
        foreach ($this->createCustomAttributes() as $customAttribute) {
            $item = $this->map($customAttribute, $savedAttributes);

            if ($item['required']) {
                array_unshift($attributes, $item);
                continue;
            }

            $attributes[] = $item;
        }

        return $this->sortAttributesByTitle($attributes);
    }

    private function map(
        \M2E\Otto\Model\Category\AbstractAttribute $attribute,
        array $savedAttributes
    ): array {
        $item = [
            'id' => $attribute->getId(),
            'title' => $attribute->getTitle(),
            'attribute_type' => $attribute->getAttributeType(),
            'type' => $attribute->isMultipleSelected() ? 'select_multiple' : 'select',
            'required' => $attribute->isRequired(),
            'min_values' => $attribute->isRequired() ? 1 : 0,
            'max_values' => $attribute->isMultipleSelected() ? count($attribute->getAllowedValues()) : 1,
            'values_allowed' => $attribute->getAllowedValues(),
            'values_example' => $attribute->getExampleValues(),
            'description' => $attribute->getDescription(),
            'template_attribute' => [],
        ];

        $existsAttribute = $savedAttributes[$attribute->getId()] ?? null;
        if ($existsAttribute) {
            $item['template_attribute'] = [
                'id' => $existsAttribute->getAttributeId(),
                'template_category_id' => $existsAttribute->getId(),
                'mode' => '1',
                'attribute_title' => $existsAttribute->getAttributeName(),
                'value_mode' => $existsAttribute->getValueMode(),
                'value_otto_recommended' => $existsAttribute->getRecommendedValue(),
                'value_custom_value' => $existsAttribute->getCustomValue(),
                'value_custom_attribute' => $existsAttribute->getCustomAttributeValue(),
            ];
        }

        return $item;
    }

    private function loadSavedAttributes(
        \M2E\Otto\Model\Category $category,
        array $typeFilter = []
    ): array {
        $attributes = [];

        $savedAttributes = $this
            ->attributeRepository
            ->findByCategoryId($category->getId(), $typeFilter);

        foreach ($savedAttributes as $attribute) {
            $attributes[$attribute->getCategoryGroupAttributeDictionaryId()] = $attribute;
        }

        return $attributes;
    }

    private function sortAttributesByTitle(array $attributes): array
    {
        usort($attributes, function ($prev, $next) {
            return strcmp($prev['title'], $next['title']);
        });

        $requiredAttributes = [];
        foreach ($attributes as $index => $attribute) {
            if (isset($attribute['required']) && $attribute['required'] === true) {
                $requiredAttributes[] = $attribute;
                unset($attributes[$index]);
            }
        }

        return array_merge($requiredAttributes, $attributes);
    }

    public function createCustomAttributes(): array
    {
        $customAttributes = [];

        $customAttributes[] = new \M2E\Otto\Model\Category\Attribute\BrandAttribute(
            'brand',
            'Brand',
            true,
            false
        );

        $customAttributes[] = new \M2E\Otto\Model\Category\Attribute\MpnAttribute(
            'mpn',
            'MPN',
            false,
            false
        );

        $customAttributes[] = new \M2E\Otto\Model\Category\Attribute\ManufacturerAttribute(
            'manufacturer',
            'Manufacturer',
            false,
            false
        );

        return $customAttributes;
    }

    public function countCustomAttributes(): int
    {
        return count($this->createCustomAttributes());
    }

    private function convertSavedAttributesToAttributesArray(\M2E\Otto\Model\Category\Attribute $savedAttribute): array
    {
        $item = [
            'id' => $savedAttribute->getCategoryGroupAttributeDictionaryId(),
            'title' => $savedAttribute->getAttributeName(),
            'attribute_type' => $savedAttribute->getAttributeType(),
            'type' => 'select',
            'required' => false,
            'min_values' => 0,
            'max_values' => 1,
            'values_allowed' => $savedAttribute->getRecommendedValue(),
            'values_example' => [],
            'description' => null,
            'template_attribute' => [],
        ];

        $item['template_attribute'] = [
            'id' => $savedAttribute->getAttributeId(),
            'template_category_id' => $savedAttribute->getId(),
            'mode' => '1',
            'attribute_title' => $savedAttribute->getAttributeName(),
            'value_mode' => $savedAttribute->getValueMode(),
            'value_otto_recommended' => $savedAttribute->getRecommendedValue(),
            'value_custom_value' => $savedAttribute->getCustomValue(),
            'value_custom_attribute' => $savedAttribute->getCustomAttributeValue(),
        ];

        return $item;
    }
}
