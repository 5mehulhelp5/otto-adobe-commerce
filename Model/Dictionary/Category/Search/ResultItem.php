<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary\Category\Search;

class ResultItem
{
    public int $categoryId;
    public string $categoryGroupId;
    public string $title;

    public function __construct(
        int $categoryId,
        string $categoryGroupId,
        string $title
    ) {
        $this->categoryGroupId = $categoryGroupId;
        $this->categoryId = $categoryId;
        $this->title = $title;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->categoryId,
            'category_group_id' => $this->categoryGroupId,
            'path' => $this->title
        ];
    }
}
