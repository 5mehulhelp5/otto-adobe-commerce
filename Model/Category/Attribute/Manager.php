<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Category\Attribute;

class Manager
{
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\Otto\Model\Otto\Template\Category\SnapshotBuilderFactory $snapshotBuilderFactory;
    private \M2E\Otto\Model\Otto\Template\Category\DiffFactory $diffFactory;
    private \M2E\Otto\Model\Otto\Template\Category\ChangeProcessorFactory $changeProcessorFactory;
    private \M2E\Otto\Model\Otto\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private \M2E\Otto\Model\AttributeMapping\GeneralService $attributeMappingGeneralService;

    public function __construct(
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\Otto\Model\Otto\Template\Category\SnapshotBuilderFactory $snapshotBuilderFactory,
        \M2E\Otto\Model\Otto\Template\Category\DiffFactory $diffFactory,
        \M2E\Otto\Model\Otto\Template\Category\ChangeProcessorFactory $changeProcessorFactory,
        \M2E\Otto\Model\Otto\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \M2E\Otto\Model\AttributeMapping\GeneralService $attributeMappingGeneralService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->attributeRepository = $attributeRepository;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
        $this->diffFactory = $diffFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->attributeMappingGeneralService = $attributeMappingGeneralService;
    }

    /**
     * @param \M2E\Otto\Model\Category\Attribute[] $attributes
     * @param \M2E\Otto\Model\Category $category
     *
     * @return void
     * @throws \Exception
     */
    public function createOrUpdateAttributes(
        array $attributes,
        \M2E\Otto\Model\Category $category
    ): void {
        $attributesSortedById = [];
        $countOfUsedAttributes = 0;

        foreach ($attributes as $attribute) {
            $attributesSortedById[$attribute->getCategoryGroupAttributeDictionaryId()] = $attribute;
            if (
                !empty($attribute->getCustomValue())
                || !empty($attribute->getCustomAttributeValue())
                || !empty($attribute->getRecommendedValue())
            ) {
                $countOfUsedAttributes++;
            }
        }

        $oldSnapshot = $this->getSnapshot($category);

        $existedAttributes = $this->attributeRepository
            ->findByCategoryId($category->getId());

        foreach ($existedAttributes as $existedAttribute) {
            $inputAttribute = $attributesSortedById[$existedAttribute->getCategoryGroupAttributeDictionaryId()] ?? null;
            if ($inputAttribute === null) {
                continue;
            }

            $this->updateAttribute($existedAttribute, $inputAttribute);
            unset($attributesSortedById[$existedAttribute->getCategoryGroupAttributeDictionaryId()]);
        }

        foreach ($attributesSortedById as $attribute) {
            if (
                empty($attribute->getCustomValue())
                && empty($attribute->getCustomAttributeValue())
                && empty($attribute->getRecommendedValue())
            ) {
                continue;
            }
            $this->createAttribute($attribute);
        }

        $newSnapshot = $this->getSnapshot($category);

        $this->addInstruction($category, $oldSnapshot, $newSnapshot);

        $category->setUsedProductAttributes($countOfUsedAttributes);
        $category->installStateSaved();
        $this->categoryRepository->save($category);
        $this->attributeMappingGeneralService->create($category->getRelatedAttributes());
    }

    private function updateAttribute(
        \M2E\Otto\Model\Category\Attribute $existedAttribute,
        \M2E\Otto\Model\Category\Attribute $inputAttribute
    ) {
        $existedAttribute->setCategoryId($inputAttribute->getCategoryId());
        $existedAttribute->setAttributeType($inputAttribute->getAttributeType());
        $existedAttribute->setCategoryGroupAttributeDictionaryId($inputAttribute->getCategoryGroupAttributeDictionaryId());
        $existedAttribute->setAttributeName($inputAttribute->getAttributeName());
        $existedAttribute->setValueMode($inputAttribute->getValueMode());
        $existedAttribute->setRecommendedValue($inputAttribute->getRecommendedValue());
        $existedAttribute->setCustomValue($inputAttribute->getCustomValue());
        $existedAttribute->setCustomAttributeValue($inputAttribute->getCustomAttributeValue());

        $this->attributeRepository->save($existedAttribute);
    }

    private function createAttribute(\M2E\Otto\Model\Category\Attribute $attribute)
    {
        $this->attributeRepository->create($attribute);
    }

    private function getSnapshot(\M2E\Otto\Model\Category $category): array
    {
        return $this->snapshotBuilderFactory
            ->create()
            ->setModel($category)
            ->getSnapshot();
    }

    private function addInstruction(
        \M2E\Otto\Model\Category $category,
        array $oldSnapshot,
        array $newSnapshot
    ): void {
        $diff = $this->diffFactory->create();
        $diff->setOldSnapshot($oldSnapshot);
        $diff->setNewSnapshot($newSnapshot);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($category);

        $changeProcessor = $this->changeProcessorFactory->create();
        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );
    }
}
