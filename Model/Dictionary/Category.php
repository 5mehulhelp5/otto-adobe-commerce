<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary;

use M2E\Otto\Model\ResourceModel\Dictionary\Category as CategoryDictionaryResource;

class Category extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(CategoryDictionaryResource::class);
    }

    public function create(
        string $categoryGroupId,
        string $title
    ): self {
        $this->setCategoryGroupId($categoryGroupId);
        $this->setTitle($title);

        return $this;
    }

    public function getId(): int
    {
        return (int)$this->getData(CategoryDictionaryResource::COLUMN_ID);
    }

    public function setCategoryGroupId(string $categoryGroupId): void
    {
        $this->setData(CategoryDictionaryResource::COLUMN_CATEGORY_GROUP_ID, $categoryGroupId);
    }

    public function getCategoryGroupId(): string
    {
        return $this->getData(CategoryDictionaryResource::COLUMN_CATEGORY_GROUP_ID);
    }

    public function setTitle(string $title): void
    {
        $this->setData(CategoryDictionaryResource::COLUMN_TITLE, $title);
    }

    public function getTitle(): string
    {
        return $this->getData(CategoryDictionaryResource::COLUMN_TITLE);
    }
}
