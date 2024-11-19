<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Category;

class Category
{
    private string $categoryGroupId;
    private string $title;

    public function __construct(string $categoryGroupId, string $title)
    {
        $this->categoryGroupId = $categoryGroupId;
        $this->title = $title;
    }

    public function getCategoryGroupId(): string
    {
        return $this->categoryGroupId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
