<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary\Category;

use M2E\Otto\Model\Dictionary\CategoryFactory;
use M2E\Otto\Model\ResourceModel\Dictionary\Category as CategoryDictionaryResource;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Dictionary\Category\CollectionFactory $collectionFactory;
    private \M2E\Otto\Model\Dictionary\CategoryFactory $categoryFactory;
    private CategoryDictionaryResource $categoryResource;
    private \Magento\Framework\App\ResourceConnection $resource;

    public function __construct(
        CategoryDictionaryResource $categoryResource,
        \M2E\Otto\Model\Dictionary\CategoryFactory $categoryFactory,
        \M2E\Otto\Model\ResourceModel\Dictionary\Category\CollectionFactory $collectionFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
        $this->categoryResource = $categoryResource;
        $this->categoryFactory = $categoryFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param \M2E\Otto\Model\Dictionary\Category[] $category
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function batchInsert(array $categories): void
    {
        $insertData = [];
        foreach ($categories as $category) {
            $insertData[] = [
                CategoryDictionaryResource::COLUMN_CATEGORY_GROUP_ID => $category->getCategoryGroupId(),
                CategoryDictionaryResource::COLUMN_TITLE => $category->getTitle()
            ];
        }

        $collection = $this->collectionFactory->create();
        $resource = $collection->getResource();

        foreach (array_chunk($insertData, 500) as $chunk) {
            $resource->getConnection()->insertMultiple($resource->getMainTable(), $chunk);
        }
    }

    public function getCategoriesByCategoryGroupId(string $categoryGroupId): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
            ['eq' => $categoryGroupId]
        );

        return array_values($collection->getItems());
    }
    public function find(int $id): ?\M2E\Otto\Model\Dictionary\Category
    {
        $category = $this->categoryFactory->create();
        $this->categoryResource->load($category, $id);

        if ($category->isObjectNew()) {
            return null;
        }

        return $category;
    }

    public function getCategoryDictionaryIdByTitle(string $title): int
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryDictionaryResource::COLUMN_TITLE,
            ['eq' => $title]
        );
        $category = $collection->getFirstItem();

        return (int)$category->getId();
    }

    public function clearTable(): void
    {
        $connection = $this->resource->getConnection();
        $connection->delete($this->categoryResource->getMainTable());
    }

    public function findByCategoryGroupIdAndTitle(string $categoryGroupId, string $title): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            CategoryDictionaryResource::COLUMN_TITLE,
            ['eq' => $title]
        );
        $collection->addFieldToFilter(
            CategoryDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
            ['eq' => $categoryGroupId]
        );

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\Otto\Model\Dictionary\Category[]
     */
    public function searchByTitle(string $query, int $limit): array
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            [CategoryDictionaryResource::COLUMN_TITLE],
            [['like' => "%$query%"]]
        );

        $collection->setPageSize($limit);

        return array_values($collection->getItems());
    }
}
