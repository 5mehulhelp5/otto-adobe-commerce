<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Category;

class CategoryGroup
{
    private string $id;
    private string $title;
    private string $productTitlePattern;
    public function __construct(
        string $id,
        string $title,
        string $productTitlePattern
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->productTitlePattern = $productTitlePattern;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getProductTitlePattern(): string
    {
        return $this->productTitlePattern;
    }
}
