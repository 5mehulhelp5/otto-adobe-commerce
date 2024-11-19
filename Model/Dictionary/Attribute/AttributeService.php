<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dictionary\Attribute;

class AttributeService
{
    private \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository;
    private \M2E\Otto\Model\Dictionary\AttributeFactory $attributeFactory;
    public function __construct(
        \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository,
        \M2E\Otto\Model\Dictionary\AttributeFactory     $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeDictionaryRepository = $attributeDictionaryRepository;
    }

    public function getHasRequiredAttributes(
        array $categoryData
    ): bool {
        foreach ($categoryData as $attribute) {
            if ($attribute->isRequired()) {
                return true;
            }
        }

        return false;
    }

    public function updateOrCreateAttributes(
        array $newAttributesData,
        array $oldAttributesData,
        string $categoryGroupId
    ): void {
        foreach ($newAttributesData as $newAttribute) {
            $existingAttribute = $this->findExistingAttributeByTitle($oldAttributesData, $newAttribute->getTitle());

            if ($existingAttribute) {
                $this->updateAttribute($existingAttribute, $newAttribute);
            } else {
                $this->createAttribute($newAttribute, $categoryGroupId);
            }
        }
    }

    private function updateAttribute(
        \M2E\Otto\Model\Dictionary\Attribute $existingAttribute,
        \M2E\Otto\Model\Otto\Connector\Attribute\Attribute $newAttribute
    ): void {
        $existingAttribute->setDescription($newAttribute->getDescription());
        $existingAttribute->setType($newAttribute->getType());
        $existingAttribute->setIsRequired($newAttribute->isRequired());
        $existingAttribute->setIsMultipleSelected($newAttribute->getIsMultipleSelected());
        $existingAttribute->setAllowedValues($newAttribute->getAllowedValues());
        $existingAttribute->setExampleValues($newAttribute->getExampleValues());
        $existingAttribute->setRelevance($newAttribute->getRelevance());
        $existingAttribute->setRequiredMediaTypes($newAttribute->getRequiredMediaTypes());
        $existingAttribute->setUnit($newAttribute->getUnit());

        $this->attributeDictionaryRepository->save($existingAttribute);
    }

    public function createAttribute(
        \M2E\Otto\Model\Otto\Connector\Attribute\Attribute $attributeData,
        string $categoryGroupId
    ): \M2E\Otto\Model\Dictionary\Attribute {
        $attribute = $this->attributeFactory->create()->create(
            $categoryGroupId,
            $attributeData->getTitle(),
            $attributeData->getDescription(),
            $attributeData->getType(),
            $attributeData->isRequired(),
            $attributeData->getIsMultipleSelected(),
            $attributeData->getAllowedValues(),
            $attributeData->getExampleValues(),
            $attributeData->getRelevance(),
            $attributeData->getRequiredMediaTypes(),
            $attributeData->getUnit()
        );
        $this->attributeDictionaryRepository->save($attribute);

        return $attribute;
    }

    private function findExistingAttributeByTitle(array $existAttributesData, string $title): ?\M2E\Otto\Model\Dictionary\Attribute
    {
        foreach ($existAttributesData as $existAttribute) {
            if ($existAttribute->getTitle() === $title) {
                return $existAttribute;
            }
        }

        return null;
    }

    public function deleteMissingAttributesByIds(array $missingAttributesIds): void
    {
        foreach ($missingAttributesIds as $attributeId) {
            $attribute = $this->attributeDictionaryRepository->find($attributeId);
            $this->attributeDictionaryRepository->delete($attribute);
        }
    }
}
