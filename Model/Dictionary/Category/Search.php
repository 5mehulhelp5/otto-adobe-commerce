<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary\Category;

use M2E\Otto\Model\Dictionary\Category\Search\ResultCollection;
use M2E\Otto\Model\Dictionary\Category\Search\ResultItem;

class Search
{
    private \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository;

    public function __construct(
        \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository
    ) {
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
    }

    public function process(string $searchQuery, int $limit): ResultCollection
    {
        $resultCollection = new ResultCollection($limit);
        $foundedItems = $this->categoryDictionaryRepository->searchByTitle($searchQuery, $limit);
        if (count($foundedItems) === 0) {
            return $resultCollection;
        }

        foreach ($foundedItems as $item) {
            $this->addItem($resultCollection, $item);
        }

        return $resultCollection;
    }

    private function addItem(ResultCollection $resultCollection, \M2E\Otto\Model\Dictionary\Category $treeItem): void
    {
        $resultCollection->add(
            new ResultItem(
                $treeItem->getId(),
                $treeItem->getCategoryGroupId(),
                $treeItem->getTitle()
            )
        );
    }
}
