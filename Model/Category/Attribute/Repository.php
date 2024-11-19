<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Category\Attribute;

use M2E\Otto\Model\ResourceModel\Category as CategoryResource;
use M2E\Otto\Model\ResourceModel\Category\Attribute as AttributeResource;
use M2E\Otto\Model\Category\Attribute;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Category\Attribute\CollectionFactory $attributeCollectionFactory;
    private AttributeResource $attributeResource;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Category\Attribute\CollectionFactory $attributeCollectionFactory,
        AttributeResource $attributeResource
    ) {
        $this->attributeResource = $attributeResource;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    public function create(\M2E\Otto\Model\Category\Attribute $entity): void
    {
        $this->attributeResource->save($entity);
    }

    public function save(\M2E\Otto\Model\Category\Attribute $attrEntity): void
    {
        $this->attributeResource->save($attrEntity);
    }

    public function delete(\M2E\Otto\Model\Category\Attribute $attrEntity): void
    {
        $this->attributeResource->delete($attrEntity);
    }

    /**
     * @return Attribute[]
     */
    public function findByCategoryId(
        int $categoryId,
        array $typeFilter = []
    ): array {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_ID,
            ['eq' => $categoryId]
        );

        if (!empty($typeFilter)) {
            $collection->addFieldToFilter(
                AttributeResource::COLUMN_ATTRIBUTE_TYPE,
                ['in' => $typeFilter]
            );
        }

        return array_values($collection->getItems());
    }

    public function findProductAttributes(int $categoryId): array
    {
        return $this->findByCategoryId($categoryId, [\M2E\Otto\Model\Category\Attribute::ATTRIBUTE_TYPE_PRODUCT]);
    }

    public function findMpnAttribute(int $categoryId): ?\M2E\Otto\Model\Category\Attribute
    {
        $attributes = $this->findByCategoryId(
            $categoryId,
            [\M2E\Otto\Model\Category\Attribute::ATTRIBUTE_TYPE_MPN]
        );

        if (empty($attributes)) {
            return null;
        }

        return reset($attributes);
    }

    public function findBrandAttribute(int $categoryId): ?\M2E\Otto\Model\Category\Attribute
    {
        $attributes = $this->findByCategoryId(
            $categoryId,
            [\M2E\Otto\Model\Category\Attribute::ATTRIBUTE_TYPE_BRAND]
        );

        if (empty($attributes)) {
            return null;
        }

        return reset($attributes);
    }

    public function findManufactureAttribute(int $categoryId): ?\M2E\Otto\Model\Category\Attribute
    {
        $attributes = $this->findByCategoryId(
            $categoryId,
            [\M2E\Otto\Model\Category\Attribute::ATTRIBUTE_TYPE_MANUFACTURER]
        );

        if (empty($attributes)) {
            return null;
        }

        return reset($attributes);
    }

    public function getCountByCategoryId(int $categoryId): int
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_ID,
            $categoryId
        );

        return $collection->getSize();
    }

    public function deleteByCategoryAttributeDictionaryIds(array $categoryAttributeDictionaryIds): void
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY_ID,
            ['in' => $categoryAttributeDictionaryIds]
        );

        foreach ($collection->getItems() as $attribute) {
            /** @var \M2E\Otto\Model\Category\Attribute $attribute */
            $this->delete($attribute);
        }
    }
}
