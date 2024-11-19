<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class GetCategories extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository;

    public function __construct(
        \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository
    ) {
        parent::__construct();

        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
    }

    public function execute()
    {
        $categoryGroupId = $this->getRequest()->getParam('category_group_id');

        $categories = $this->getCategories($categoryGroupId);

        $response = [];
        foreach ($categories as $category) {
            $response[] = [
                'category_dictionary_id' => $category->getId(),
                'category_group_id' => $category->getCategoryGroupId(),
                'title' => $category->getTitle()
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }

    private function getCategories(string $categoryGroupId): array
    {
        return $this->categoryDictionaryRepository->getCategoriesByCategoryGroupId($categoryGroupId);
    }
}
