<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary\Attribute;

use M2E\Otto\Model\ResourceModel\Dictionary\Attribute as AttributeDictionaryResource;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Dictionary\Attribute\CollectionFactory $collectionFactory;
    private \M2E\Otto\Model\ResourceModel\Dictionary\Attribute $attributeResource;
    private \M2E\Otto\Model\Dictionary\AttributeFactory $attributeFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Dictionary\Attribute\CollectionFactory $collectionFactory,
        \M2E\Otto\Model\ResourceModel\Dictionary\Attribute $attributeResource,
        \M2E\Otto\Model\Dictionary\AttributeFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeResource = $attributeResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function find(int $id): ?\M2E\Otto\Model\Dictionary\Attribute
    {
        $attribute = $this->attributeFactory->create();
        $this->attributeResource->load($attribute, $id);

        if ($attribute->isObjectNew()) {
            return null;
        }

        return $attribute;
    }

    /**
     * @param string $categoryGroupId
     *
     * @return \M2E\Otto\Model\Dictionary\Attribute[]
     */
    public function getAttributesByCategoryGroupId(string $categoryGroupId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            AttributeDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
            ['eq' => $categoryGroupId]
        );

        return array_values($collection->getItems());
    }

    public function getByCategoryGroupIdAndTitle(string $categoryGroupId, string $title): ?\M2E\Otto\Model\Dictionary\Attribute
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            AttributeDictionaryResource::COLUMN_TITLE,
            ['eq' => $title]
        );
        $collection->addFieldToFilter(
            AttributeDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
            ['eq' => $categoryGroupId]
        );

        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function getAttributesCountByCategoryGroupId(string $categoryGroupId): int
    {
        return count($this->getAttributesByCategoryGroupId($categoryGroupId));
    }

    public function save(\M2E\Otto\Model\Dictionary\Attribute $attribute): void
    {
        $this->attributeResource->save($attribute);
    }

    public function delete(\M2E\Otto\Model\Dictionary\Attribute $attribute): void
    {
        $this->attributeResource->delete($attribute);
    }
}
