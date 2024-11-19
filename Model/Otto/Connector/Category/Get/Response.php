<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Category\Get;

class Response
{
    /** @var \M2E\Otto\Model\Otto\Connector\Category\Category[] */
    private array $categories = [];
    /** @var \M2E\Otto\Model\Otto\Connector\Category\CategoryGroup[] */
    private array $categoryGroups = [];

    public function addCategory(
        \M2E\Otto\Model\Otto\Connector\Category\Category $category
    ): void {
        $this->categories[] = $category;
    }

    public function addCategoryGroup(
        \M2E\Otto\Model\Otto\Connector\Category\CategoryGroup $categoryGroup
    ): void {
        $this->categoryGroups[] = $categoryGroup;
    }

    /**
     * @return \M2E\Otto\Model\Otto\Connector\Category\Category[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @return \M2E\Otto\Model\Otto\Connector\Category\CategoryGroup[]
     */
    public function getCategoryGroups(): array
    {
        return $this->categoryGroups;
    }
}
