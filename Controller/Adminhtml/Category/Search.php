<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Category;

class Search extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private const SEARCH_LIMIT = 20;

    private \M2E\Otto\Model\Dictionary\Category\Search $categorySearch;

    public function __construct(
        \M2E\Otto\Model\Dictionary\Category\Search $categorySearch
    ) {
        parent::__construct();

        $this->categorySearch = $categorySearch;
    }

    public function execute()
    {
        $searchQuery = $this->getRequest()->getParam('search_query');

        $result = [
            'categories' => [],
            'has_more' => false,
        ];

        if (empty($searchQuery)) {
            $this->setJsonContent($result);

            return $this->getResult();
        }

        $searchResult = $this->categorySearch->process($searchQuery, self::SEARCH_LIMIT + 1);

        $result['categories'] = array_map(static function (\M2E\Otto\Model\Dictionary\Category\Search\ResultItem $item) {
            return [
                'id' => $item->categoryId,
                'category_group_id' => $item->categoryGroupId,
                'path' => $item->title
            ];
        }, $searchResult->getAll());

        $result['has_more'] = count($result['categories']) > self::SEARCH_LIMIT;

        $this->setJsonContent($result);

        return $this->getResult();
    }
}
