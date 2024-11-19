<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary;

use M2E\Otto\Model\ResourceModel\Dictionary\Attribute as AttributeDictionaryResource;

class Attribute extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(AttributeDictionaryResource::class);
    }

    public function create(
        string $categoryGroupId,
        string $title,
        ?string $description,
        string $type,
        bool $isRequired,
        bool $isMultipleSelected,
        array $allowedValues,
        array $exampleValues,
        ?string $relevance,
        array $requiredMediaTypes,
        ?string $unit
    ): self {
        $this->setCategoryGroupId($categoryGroupId);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setType($type);
        $this->setIsRequired($isRequired);
        $this->setIsMultipleSelected($isMultipleSelected);
        $this->setAllowedValues($allowedValues);
        $this->setExampleValues($exampleValues);
        $this->setRelevance($relevance);
        $this->setRequiredMediaTypes($requiredMediaTypes);
        $this->setUnit($unit);

        return $this;
    }

    public function setCategoryGroupId(string $categoryGroupId): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_CATEGORY_GROUP_ID, $categoryGroupId);
    }

    public function setTitle(string $title): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_TITLE, $title);
    }

    public function setDescription(?string $description): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_DESCRIPTION, $description);
    }

    public function setType(string $type): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_TYPE, $type);
    }

    public function setIsRequired(bool $isRequired): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_IS_REQUIRED, $isRequired);
    }

    public function setIsMultipleSelected(bool $isMultipleSelected): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_IS_MULTIPLE_SELECTED, $isMultipleSelected);
    }

    public function setAllowedValues(array $values): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_ALLOWED_VALUES, json_encode($values));
    }

    public function setExampleValues(array $exampleValues): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_EXAMPLE_VALUES, json_encode($exampleValues));
    }

    public function setRelevance(?string $relevance): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_RELEVANCE, $relevance);
    }

    public function setRequiredMediaTypes(array $requiredMediaTypes): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_REQUIRED_MEDIA_TYPES, json_encode($requiredMediaTypes));
    }

    public function setUnit(?string $unit): void
    {
        $this->setData(AttributeDictionaryResource::COLUMN_UNIT, $unit);
    }

    public function getCategoryGroupId(): string
    {
        return $this->getData(AttributeDictionaryResource::COLUMN_CATEGORY_GROUP_ID);
    }

    public function getTitle(): string
    {
        return $this->getData(AttributeDictionaryResource::COLUMN_TITLE);
    }

    public function getDescription(): ?string
    {
        return $this->getData(AttributeDictionaryResource::COLUMN_DESCRIPTION);
    }

    public function getType(): string
    {
        return $this->getData(AttributeDictionaryResource::COLUMN_TYPE);
    }

    public function isRequired(): bool
    {
        return (bool)$this->getData(AttributeDictionaryResource::COLUMN_IS_REQUIRED);
    }

    public function isMultipleSelected(): bool
    {
        return (bool)$this->getData(AttributeDictionaryResource::COLUMN_IS_MULTIPLE_SELECTED);
    }

    public function getAllowedValues(): array
    {
        return json_decode($this->getData(AttributeDictionaryResource::COLUMN_ALLOWED_VALUES), true);
    }

    public function getExampleValues(): array
    {
        return json_decode($this->getData(AttributeDictionaryResource::COLUMN_EXAMPLE_VALUES), true);
    }

    public function getRelevance(): ?string
    {
        return $this->getData(AttributeDictionaryResource::COLUMN_RELEVANCE);
    }

    public function getRequiredMediaTypes(): array
    {
        return json_decode($this->getData(AttributeDictionaryResource::COLUMN_REQUIRED_MEDIA_TYPES), true);
    }

    public function getUnit(): ?string
    {
        return $this->getData(AttributeDictionaryResource::COLUMN_UNIT);
    }
}
