<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Category;

use M2E\Otto\Model\ResourceModel\Category as CategoryResource;

class Repository
{
    private CategoryResource $categoryResource;
    private CategoryResource\CollectionFactory $categoryCollectionFactory;
    private \M2E\Otto\Model\CategoryFactory $categoryFactory;

    public function __construct(
        \M2E\Otto\Model\CategoryFactory $categoryFactory,
        CategoryResource\CollectionFactory $categoryCollectionFactory,
        CategoryResource $categoryResource
    ) {
        $this->categoryResource = $categoryResource;
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @return \M2E\Otto\Model\Category[]
     */
    public function getAll()
    {
        $collection = $this->categoryCollectionFactory->create();

        return array_values($collection->getItems());
    }

    public function get(int $id): \M2E\Otto\Model\Category
    {
        $category = $this->find($id);
        if ($category === null) {
            throw new \LogicException('Category with id ' . $id . ' not found');
        }

        return $category;
    }

    public function find(int $id): ?\M2E\Otto\Model\Category
    {
        $category = $this->categoryFactory->create();
        $this->categoryResource->load($category, $id);

        if ($category->isObjectNew()) {
            return null;
        }

        return $category;
    }

    public function save(\M2E\Otto\Model\Category $category): void
    {
        $category->setUpdateDate(\M2E\Core\Helper\Date::createCurrentGmt());

        $this->categoryResource->save($category);
    }

    public function delete(\M2E\Otto\Model\Category $category): void
    {
        $this->categoryResource->delete($category);
    }

    /**
     * @param int|string[] $ids
     *
     * @return \M2E\Otto\Model\Category[]
     */
    public function getItems(array $ids): array
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToFilter(CategoryResource::COLUMN_ID, ['in' => $ids]);

        return array_values($collection->getItems());
    }

    public function findByCategoryGroupIdAndTitle(string $categoryGroupId, string $title): ?\M2E\Otto\Model\Category
    {
        $collection = $this->categoryCollectionFactory->create();

        $collection->addFieldToFilter(
            CategoryResource::COLUMN_CATEGORY_GROUP_ID,
            ['eq' => $categoryGroupId]
        );

        $collection->addFieldToFilter(
            CategoryResource::COLUMN_TITLE,
            ['eq' => $title]
        );

        $category = $collection->getFirstItem();
        if ($category->isObjectNew()) {
            return null;
        }

        return $category;
    }
}
