<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AttributeMapping\Gpsr;

class CategoryModifier
{
    private const COUNT_CATEGORIES_FOR_CYCLE = 50;

    private \M2E\Otto\Model\ResourceModel\Template\Category\CollectionFactory $templateCategoryCollectionFactory;
    private \M2E\Otto\Model\Otto\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private \M2E\Otto\Model\AttributeMapping\Gpsr\CategoryModifier\CategoryDiffStub $categoryDiffStub;
    private \M2E\Otto\Model\Otto\Template\Category\ChangeProcessorFactory $changeProcessorFactory;
    private \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Template\Category\CollectionFactory $templateCategoryCollectionFactory,
        \M2E\Otto\Model\Otto\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \M2E\Otto\Model\AttributeMapping\Gpsr\CategoryModifier\CategoryDiffStub $categoryDiffStub,
        \M2E\Otto\Model\Otto\Template\Category\ChangeProcessorFactory $changeProcessorFactory,
        \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository
    ) {
        $this->templateCategoryCollectionFactory = $templateCategoryCollectionFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->categoryDiffStub = $categoryDiffStub;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param \M2E\Otto\Model\AttributeMapping\Gpsr\Pair[] $gpsrAttributes
     *
     * @return void
     */
    public function process(array $gpsrAttributes): void
    {
        $categoryTemplateId = 0;
        do {
            $categories = $this->getCategories($categoryTemplateId);
            foreach ($categories as $category) {
                $categoryTemplateId = (int)$category->getId();

                $isChangedCategory = $this->processCategory($category, $gpsrAttributes);
                if (!$isChangedCategory) {
                    continue;
                }

                $this->createProductInstruction($category);
            }
        } while (count($categories) === self::COUNT_CATEGORIES_FOR_CYCLE);
    }

    /**
     * @param int $fromId
     *
     * @return \M2E\Otto\Model\Otto\Template\Category[]
     */
    private function getCategories(int $fromId): array
    {
        $collection = $this->templateCategoryCollectionFactory->create();
        $collection->addFieldToFilter('id', ['gt' => $fromId]);
        $collection->setOrder('id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $collection->setPageSize(50);

        return array_values($collection->getItems());
    }

    /**
     * @param \M2E\Otto\Model\Otto\Template\Category $category
     * @param \M2E\Otto\Model\AttributeMapping\Gpsr\Pair[] $gpsrAttributes
     *
     * @return void
     */
    private function processCategory(\M2E\Otto\Model\Otto\Template\Category $category, array $gpsrAttributes): bool
    {
        $specificsByName = $this->getSpecificsByName($category);

        $isChangedCategory = false;
        foreach ($gpsrAttributes as $gpsrAttribute) {
            $attribute = $specificsByName[$gpsrAttribute->channelAttributeTitle] ?? null;

            if ($attribute === null) {
                continue;
            }

            if ($attribute->isValueModeNone()) {
                $attribute->setCustomAttributeValue($gpsrAttribute->magentoAttributeCode);
                $attribute->setValueCustomAttributeMode();
                $this->attributeRepository->save($attribute);

                $isChangedCategory = true;

                continue;
            }

            if (
                $attribute->isValueModeCustomAttribute()
                && $attribute->getCustomAttributeValue() !== $gpsrAttribute->magentoAttributeCode
            ) {
                $attribute->setCustomAttributeValue($gpsrAttribute->magentoAttributeCode);
                $this->attributeRepository->save($attribute);

                $isChangedCategory = true;
            }
        }

        return $isChangedCategory;
    }

    /**
     * @param \M2E\Otto\Model\Otto\Template\Category $category
     *
     * @return \M2E\Otto\Model\Category\Attribute[]
     */
    private function getSpecificsByName(\M2E\Otto\Model\Otto\Template\Category $category): array
    {
        $result = [];
        foreach ($category->getAttributes() as $attribute) {
            $result[$attribute->getAttributeName()] = $attribute;
        }

        return $result;
    }

    private function createProductInstruction(\M2E\Otto\Model\Otto\Template\Category $category): void
    {
        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($category);

        $changeProcessor = $this->changeProcessorFactory->create();
        $changeProcessor->process(
            $this->categoryDiffStub,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );
    }
}
