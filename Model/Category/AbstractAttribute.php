<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Category;

abstract class AbstractAttribute
{
    private $id;
    private string $title;
    private bool $isRequired;
    private bool $isMultipleSelected;
    private array $allowedValues;
    private array $exampleValues;
    private ?string $description;

    public function __construct(
        $id,
        string $title,
        bool $isRequired,
        bool $isMultipleSelected,
        ?string $description = null,
        array $allowedValues = [],
        array $exampleValues = []
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->isRequired = $isRequired;
        $this->isMultipleSelected = $isMultipleSelected;
        $this->allowedValues = $allowedValues;
        $this->exampleValues = $exampleValues;
        $this->description = $description;
    }

    abstract public function getAttributeType(): string;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function isMultipleSelected(): bool
    {
        return $this->isMultipleSelected;
    }

    public function getAllowedValues(): array
    {
        return $this->allowedValues;
    }

    public function getExampleValues(): array
    {
        return $this->exampleValues;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
