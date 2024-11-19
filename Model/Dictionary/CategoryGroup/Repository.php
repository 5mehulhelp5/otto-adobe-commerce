<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary\CategoryGroup;

use M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup as CategoryGroupDictionaryResource;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup\CollectionFactory $collectionFactory;
    private \M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup $categoryGroupResource;
    private \Magento\Framework\App\ResourceConnection $resource;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup\CollectionFactory $collectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup $categoryGroupResource
    ) {
        $this->categoryGroupResource = $categoryGroupResource;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param \M2E\Otto\Model\Dictionary\CategoryGroup[] $categoryGroups
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function batchInsert(array $categoryGroups): void
    {
        $insertData = [];
        foreach ($categoryGroups as $categoryGroup) {
            $insertData[] = [
                CategoryGroupDictionaryResource::COLUMN_CATEGORY_GROUP_ID => $categoryGroup->getCategoryGroupId(),
                CategoryGroupDictionaryResource::COLUMN_TITLE => $categoryGroup->getTitle(),
                CategoryGroupDictionaryResource::COLUMN_PRODUCT_TITLE_PATTERN => $categoryGroup->getProductTitlePattern()
            ];
        }

        $collection = $this->collectionFactory->create();
        $resource = $collection->getResource();

        foreach (array_chunk($insertData, 500) as $chunk) {
            $resource->getConnection()->insertMultiple($resource->getMainTable(), $chunk);
        }
    }

    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    public function getByCategoryGroupId(string $categoryGroupId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(CategoryGroupDictionaryResource::COLUMN_CATEGORY_GROUP_ID, $categoryGroupId);

        return array_values($collection->getItems());
    }

    public function isCategoryGroupExist(string $categoryGroupId): bool
    {
        return !empty($this->getByCategoryGroupId($categoryGroupId));
    }

    public function clearTable(): void
    {
        $connection = $this->resource->getConnection();
        $connection->delete($this->categoryGroupResource->getMainTable());
    }
}
