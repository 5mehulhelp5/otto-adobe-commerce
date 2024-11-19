<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class GetEditedCategoryInfo extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Category\CategoryLoader $categoryLoader;
    private \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository;

    public function __construct(
        \M2E\Otto\Model\Category\CategoryLoader $categoryLoader,
        \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository
    ) {
        parent::__construct();

        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->categoryLoader = $categoryLoader;
    }

    public function execute()
    {
        $categoryDictionaryId = (int)$this->getRequest()->getParam('category_dictionary_id');
        $categoryGroupId = $this->getRequest()->getParam('category_group_id');
        $categoryId = (int)$this->getRequest()->getParam('category_id');
        $title = $this->getRequest()->getParam('title');

        try {
            $category = $this->categoryLoader->getOrCreateCategory(
                $categoryGroupId,
                $categoryDictionaryId,
                $title,
                $categoryId
            );
        } catch (\Throwable $e) {
            $this->setJsonContent([
                'success' => false,
                'message' => $e->getMessage()
            ]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'success' => true,
            'title' => $category->getTitle(),
            'category_id' => $category->getId(),
            'category_group_id' => $category->getCategoryGroupId(),
            'category_dictionary_id' => $this->categoryDictionaryRepository->getCategoryDictionaryIdByTitle($category->getTitle()),
            'has_required_attributes' => $category->getHasRequiredProductAttributes(),
            'is_all_required_attributes_filled' => $category->getId() ? $category->isAllRequiredAttributesFilled($category->getCategoryGroupId()) : false
        ]);

        return $this->getResult();
    }
}
