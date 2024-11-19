<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Attribute;

class Attribute
{
    private string $title;
    private ?string $description;
    private string $type;
    private bool $isRequired;
    private bool $isMultipleSelected;
    private array $allowedValues = [];
    private array $exampleValues = [];
    private ?string $relevance;
    private array $requiredMediaTypes = [];
    private ?string $unit;

    public function __construct(
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
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->isRequired = $isRequired;
        $this->isMultipleSelected = $isMultipleSelected;
        $this->allowedValues = $allowedValues;
        $this->exampleValues = $exampleValues;
        $this->relevance = $relevance;
        $this->requiredMediaTypes = $requiredMediaTypes;
        $this->unit = $unit;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function getIsMultipleSelected(): bool
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

    public function getRelevance(): ?string
    {
        return $this->relevance;
    }

    public function getRequiredMediaTypes(): array
    {
        return $this->requiredMediaTypes;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }
}
