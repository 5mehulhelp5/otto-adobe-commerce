<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Category;

class GetCategoryGroups extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractCategory
{
    private \M2E\Otto\Model\Dictionary\CategoryGroup\Repository $categoryGroupDictionaryRepository;
    private \M2E\Otto\Model\CategoryDictionaryService $categoryDictionaryService;

    public function __construct(
        \M2E\Otto\Model\CategoryDictionaryService $categoryDictionaryService,
        \M2E\Otto\Model\Dictionary\CategoryGroup\Repository $categoryGroupDictionaryRepository
    ) {
        parent::__construct();

        $this->categoryGroupDictionaryRepository = $categoryGroupDictionaryRepository;
        $this->categoryDictionaryService = $categoryDictionaryService;
    }

    public function execute()
    {
        $categoryGroups = $this->getCategoryGroups();

        $response = [];
        foreach ($categoryGroups as $categoryGroup) {
            $response[] = [
                'category_group_id' => $categoryGroup->getCategoryGroupId(),
                'title' => $categoryGroup->getTitle()
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }

    private function getCategoryGroups(): array
    {
        $this->categoryDictionaryService->loadCategoryDataIfNeed();

        return $this->categoryGroupDictionaryRepository->getAll();
    }
}
