<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

class CategoryDictionaryService
{
    private \M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup\CollectionFactory $categoryGroupCollection;
    private \M2E\Otto\Model\Otto\Connector\Category\Get\Processor $categoryProcessor;
    private \M2E\Otto\Model\Dictionary\CategoryGroupFactory $categoryGroupFactory;
    private \M2E\Otto\Model\Dictionary\CategoryFactory $categoryFactory;
    private \M2E\Otto\Model\Dictionary\CategoryGroup\Repository $categoryGroupDictionaryRepository;
    private \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository;
    private \M2E\Otto\Model\Otto\Connector\Attribute\Get\Processor $attributeProcessor;

    public function __construct(
        \M2E\Otto\Model\Otto\Connector\Category\Get\Processor $categoryProcessor,
        \M2E\Otto\Model\Otto\Connector\Attribute\Get\Processor $attributeProcessor,
        \M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup\CollectionFactory $categoryGroupCollection,
        \M2E\Otto\Model\Dictionary\CategoryGroupFactory $categoryGroupFactory,
        \M2E\Otto\Model\Dictionary\CategoryFactory $categoryFactory,
        \M2E\Otto\Model\Dictionary\CategoryGroup\Repository $categoryGroupDictionaryRepository,
        \M2E\Otto\Model\Dictionary\Category\Repository $categoryDictionaryRepository
    ) {
        $this->attributeProcessor = $attributeProcessor;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->categoryGroupDictionaryRepository = $categoryGroupDictionaryRepository;
        $this->categoryFactory = $categoryFactory;
        $this->categoryGroupFactory = $categoryGroupFactory;
        $this->categoryProcessor = $categoryProcessor;
        $this->categoryGroupCollection = $categoryGroupCollection;
    }

    public function loadCategoryDataIfNeed(): void
    {
        if ($this->isNeedGetData()) {
            $this->getCategoryDataFromServer();
        }
    }

    private function isNeedGetData(): bool
    {
        $categoryGroupCollection = $this->categoryGroupCollection->create();

        return $categoryGroupCollection->getSize() === 0;
    }

    public function getCategoryDataFromServer(): void
    {
        $categories = [];
        $categoryGroups = [];

        $response = $this->categoryProcessor->process();

        foreach ($response->getCategoryGroups() as $categoryGroup) {
            $categoryGroups[] = $this->categoryGroupFactory->create()->create(
                $categoryGroup->getId(),
                $categoryGroup->getTitle(),
                $categoryGroup->getProductTitlePattern()
            );
        }

        foreach ($response->getCategories() as $category) {
            $categories[] = $this->categoryFactory->create()->create(
                $category->getCategoryGroupId(),
                $category->getTitle()
            );
        }

        $this->categoryGroupDictionaryRepository->batchInsert($categoryGroups);
        $this->categoryDictionaryRepository->batchInsert($categories);
    }

    public function getAttributesFromServer(string $categoryGroupId): Otto\Connector\Attribute\Get\Response
    {
        return $this->attributeProcessor->process($categoryGroupId);
    }
}
