<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AttributeMapping\Gpsr;

use M2E\Otto\Model\Category\Attribute;

class Update
{
    private \M2E\Otto\Model\AttributeMapping\Repository $attributeMappingRepository;
    private \M2E\Otto\Model\AttributeMapping\PairFactory $mappingFactory;

    public function __construct(
        \M2E\Otto\Model\AttributeMapping\Repository $attributeMappingRepository,
        \M2E\Otto\Model\AttributeMapping\PairFactory $mappingFactory
    ) {
        $this->attributeMappingRepository = $attributeMappingRepository;
        $this->mappingFactory = $mappingFactory;
    }

    /**
     * @param \M2E\Otto\Model\AttributeMapping\Gpsr\Pair[]  $attributesMapping
     *
     * @return int - processed (updated or created) count
     */
    public function process(array $attributesMapping): int
    {
        $attributesMapping = $this->removeUnknownAttributes($attributesMapping);
        $existedByAttributeCode = $this->getExistedMappingGroupedByCode();

        $processedCount = 0;
        foreach ($attributesMapping as $newPair) {
            $exist = $existedByAttributeCode[$newPair->channelAttributeCode] ?? null;
            if ($exist === null) {
                $new = $this->mappingFactory->create(
                    \M2E\Otto\Model\AttributeMapping\GpsrService::MAPPING_TYPE,
                    $newPair->channelAttributeTitle,
                    $newPair->channelAttributeCode,
                    $newPair->magentoAttributeCode
                );

                $this->attributeMappingRepository->create($new);

                $processedCount++;

                continue;
            }

            unset($existedByAttributeCode[$newPair->channelAttributeCode]);

            if ($exist->getMagentoAttributeCode() === $newPair->magentoAttributeCode) {
                continue;
            }

            $exist->setMagentoAttributeCode($newPair->magentoAttributeCode);

            $this->attributeMappingRepository->save($exist);

            $processedCount++;
        }

        if (!empty($existedByAttributeCode)) {
            foreach ($existedByAttributeCode as $someOld) {
                $this->attributeMappingRepository->remove($someOld);
            }
        }

        return $processedCount;
    }

    /**
     * @param \M2E\Otto\Model\AttributeMapping\Gpsr\Pair[] $attributesMapping
     *
     * @return \M2E\Otto\Model\AttributeMapping\Gpsr\Pair[]
     */
    private function removeUnknownAttributes(array $attributesMapping): array
    {
        $result = [];
        $knownAttributes = \M2E\Otto\Model\AttributeMapping\Gpsr\Provider::getAllAttributesCodes();
        foreach ($attributesMapping as $pair) {
            if (!in_array($pair->channelAttributeCode, $knownAttributes, true)) {
                continue;
            }

            $result[] = $pair;
        }

        return $result;
    }

    /**
     * @return \M2E\Otto\Model\AttributeMapping\Pair[]
     */
    private function getExistedMappingGroupedByCode(): array
    {
        $result = [];

        $existed = $this->attributeMappingRepository->findByType(
            \M2E\Otto\Model\AttributeMapping\GpsrService::MAPPING_TYPE
        );
        foreach ($existed as $pair) {
            $result[$pair->getChannelAttributeCode()] = $pair;
        }

        return $result;
    }
}
