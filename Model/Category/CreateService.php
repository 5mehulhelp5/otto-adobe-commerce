<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Category;

class CreateService
{
    private \M2E\Otto\Model\CategoryDictionaryService $categoryDictionaryService;
    private \M2E\Otto\Model\CategoryFactory $categoryFactory;
    private \M2E\Otto\Model\Dictionary\Attribute\AttributeService $attributeService;
    private \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository;
    private \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository;

    public function __construct(
        \M2E\Otto\Model\Dictionary\Attribute\AttributeService $attributeService,
        \M2E\Otto\Model\CategoryDictionaryService $categoryDictionaryService,
        \M2E\Otto\Model\CategoryFactory $categoryFactory,
        \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository,
        \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository
    ) {
        $this->attributeDictionaryRepository = $attributeDictionaryRepository;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->attributeService = $attributeService;
        $this->categoryFactory = $categoryFactory;
        $this->categoryDictionaryService = $categoryDictionaryService;
    }

    public function create(
        int $categoryDictionaryId,
        string $categoryGroupId
    ): \M2E\Otto\Model\Category {

        $categoryDictionary = $this->categoryDictionaryRepository->find($categoryDictionaryId);

        $categoryData = $this->getAttributes($categoryGroupId);
        $totalProductAttributes = count($categoryData);
        $hasRequiredProductAttributes = $this->attributeService->getHasRequiredAttributes($categoryData);

        $category = $this->categoryFactory->create()->create(
            $categoryGroupId,
            $categoryDictionary->getTitle(),
            $totalProductAttributes,
            $hasRequiredProductAttributes
        );

        return $category;
    }

    private function getAttributes(string $categoryGroupId)
    {
        $attributes = $this->getFromDatabase($categoryGroupId);
        if (!empty($attributes)) {
            return $attributes;
        }

        $response = $this->categoryDictionaryService->getAttributesFromServer($categoryGroupId);

        $attributes = [];
        foreach ($response->getAttributes() as $attribute) {
            $attributes[] = $this->attributeService->createAttribute($attribute, $categoryGroupId);
        }

        return $attributes;
    }

    private function getFromDatabase(string $categoryGroupId)
    {
        return $this->attributeDictionaryRepository->getAttributesByCategoryGroupId($categoryGroupId);
    }
}
