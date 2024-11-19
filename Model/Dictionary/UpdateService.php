<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary;

class UpdateService
{
    private \M2E\Otto\Model\CategoryDictionaryService $categoryDictionaryService;
    private \M2E\Otto\Model\Dictionary\CategoryGroup\Repository $categoryGroupRepository;
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository;
    private \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository;
    private \M2E\Otto\Model\Dictionary\Attribute\AttributeService $attributeDictionaryService;
    private \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService;

    public function __construct(
        \M2E\Otto\Model\CategoryDictionaryService $categoryDictionaryService,
        \M2E\Otto\Model\Dictionary\CategoryGroup\Repository $categoryGroupRepository,
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository,
        \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository,
        \M2E\Otto\Model\Dictionary\Attribute\AttributeService $attributeDictionaryService,
        \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService
    ) {
        $this->attributeService = $attributeService;
        $this->attributeRepository = $attributeRepository;
        $this->attributeDictionaryService = $attributeDictionaryService;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->attributeDictionaryRepository = $attributeDictionaryRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categoryGroupRepository = $categoryGroupRepository;
        $this->categoryDictionaryService = $categoryDictionaryService;
    }

    public function update(): void
    {
        $this->clearDictionaries();
        $this->getCategoryData();
        $this->cleanUpCategoriesAndAttributes();
        $this->updateAttributesForExistCategories();
    }

    private function clearDictionaries(): void
    {
        $this->categoryGroupRepository->clearTable();
        $this->categoryDictionaryRepository->clearTable();
    }

    private function getCategoryData(): void
    {
        $this->categoryDictionaryService->getCategoryDataFromServer();
    }

    private function cleanUpCategoriesAndAttributes(): void
    {
        $existCategories = $this->categoryRepository->getAll();

        foreach ($existCategories as $category) {
            $categoryGroupId = $category->getCategoryGroupId();

            if (!$this->categoryGroupRepository->isCategoryGroupExist($categoryGroupId)) {
                $category->setIsDeleted(1);
                $this->categoryRepository->save($category);

                $attributes = $this->attributeDictionaryRepository->getAttributesByCategoryGroupId($categoryGroupId);

                foreach ($attributes as $attribute) {
                    $this->attributeDictionaryRepository->delete($attribute);
                }
            }

            if (
                $this->categoryGroupRepository->isCategoryGroupExist($categoryGroupId)
                && $category->getIsDeleted() === 1
                && !empty($this->categoryDictionaryRepository->findByCategoryGroupIdAndTitle($category->getCategoryGroupId(), $category->getTitle()))
            ) {
                $category->setIsDeleted(0);
                $this->categoryRepository->save($category);
            }
        }
    }

    public function updateAttributesForExistCategories(): void
    {
        $existCategories = $this->categoryRepository->getAll();
        $updatedCategoryGroups = [];

        foreach ($existCategories as $category) {
            if ($category->getIsDeleted() === 1) {
                continue;
            }

            $categoryGroupId = $category->getCategoryGroupId();
            if (in_array($categoryGroupId, $updatedCategoryGroups)) {
                continue;
            }

            $newAttributesData = $this->categoryDictionaryService->getAttributesFromServer($categoryGroupId)->getAttributes();
            $oldAttributesData = $this->attributeDictionaryRepository->getAttributesByCategoryGroupId($categoryGroupId);

            $this->attributeDictionaryService->updateOrCreateAttributes($newAttributesData, $oldAttributesData, $categoryGroupId);

            $missingAttributesIds = $this->getMissingAttributeIds($oldAttributesData, $newAttributesData);
            $this->attributeDictionaryService->deleteMissingAttributesByIds($missingAttributesIds);
            $this->attributeRepository->deleteByCategoryAttributeDictionaryIds($missingAttributesIds);

            $updatedCategoryGroups[] = $categoryGroupId;

            $category->setTotalProductAttributes(count($newAttributesData) + $this->attributeService->countCustomAttributes());
            $category->setUsedProductAttributes(count($this->attributeRepository->findByCategoryId($category->getId())));
            $this->categoryRepository->save($category);
        }
    }

    private function getMissingAttributeIds(array $oldAttributesData, array $newAttributesData): array
    {
        $missingAttributesIds = [];
        $newAttributeTitles = array_map(fn($attribute) => $attribute->getTitle(), $newAttributesData);
        foreach ($oldAttributesData as $oldAttribute) {
            if (!in_array($oldAttribute->getTitle(), $newAttributeTitles)) {
                $missingAttributesIds[] = $oldAttribute->getId();
            }
        }

        return $missingAttributesIds;
    }
}
