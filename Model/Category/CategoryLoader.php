<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Category;

use M2E\Otto\Model\Category;

class CategoryLoader
{
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\Category\CreateService $createService;
    private \M2E\Otto\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\Category\CreateService $createService,
        \M2E\Otto\Model\Account\Repository $accountRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->createService = $createService;
        $this->accountRepository = $accountRepository;
    }

    public function getOrCreateCategory(
        string $categoryGroupId,
        int $categoryDictionaryId,
        ?string $title,
        ?int $categoryId
    ): Category {
        $entity = $this->categoryRepository->find($categoryId);
        if ($entity !== null) {
            return $entity;
        }

        if (!empty($title)) {
            $entity = $this->categoryRepository->findByCategoryGroupIdAndTitle($categoryGroupId, $title);
            if ($entity !== null) {
                return $entity;
            }
        }

        return $this->createService->create($categoryDictionaryId, $categoryGroupId);
    }
}
