<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary;

use M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup as CategoryGroupDictionaryResource;

class CategoryGroup extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(CategoryGroupDictionaryResource::class);
    }

    public function create(
        string $categoryGroupId,
        string $title,
        string $productTitlePattern
    ): self {
        $this->setCategoryGroupId($categoryGroupId);
        $this->setTitle($title);
        $this->setProductTitlePattern($productTitlePattern);

        return $this;
    }

    public function setCategoryGroupId(string $categoryGroupId): void
    {
        $this->setData(CategoryGroupDictionaryResource::COLUMN_CATEGORY_GROUP_ID, $categoryGroupId);
    }

    public function setTitle(string $title): void
    {
        $this->setData(CategoryGroupDictionaryResource::COLUMN_TITLE, $title);
    }

    public function setProductTitlePattern(string $productTitlePattern): void
    {
        $this->setData(CategoryGroupDictionaryResource::COLUMN_PRODUCT_TITLE_PATTERN, $productTitlePattern);
    }

    public function getCategoryGroupId(): string
    {
        return $this->getData(CategoryGroupDictionaryResource::COLUMN_CATEGORY_GROUP_ID);
    }

    public function getTitle(): string
    {
        return $this->getData(CategoryGroupDictionaryResource::COLUMN_TITLE);
    }

    public function getProductTitlePattern(): string
    {
        return $this->getData(CategoryGroupDictionaryResource::COLUMN_PRODUCT_TITLE_PATTERN);
    }
}
