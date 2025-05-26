<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AttributeMapping\Gpsr;

use M2E\Otto\Model\AttributeMapping\GpsrService;

class Update
{
    private \M2E\Core\Model\AttributeMapping\Adapter $attributeMappingAdapter;
    private \M2E\Core\Model\AttributeMapping\AdapterFactory $attributeMappingAdapterFactory;

    public function __construct(
        \M2E\Core\Model\AttributeMapping\AdapterFactory $attributeMappingAdapterFactory
    ) {
        $this->attributeMappingAdapterFactory = $attributeMappingAdapterFactory;
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

        $new = [];
        $exists = [];
        foreach ($attributesMapping as $newPair) {
            $exist = $existedByAttributeCode[$newPair->channelAttributeCode] ?? null;
            if ($exist === null) {
                $new[] = $this->getAdapter()->createPair(
                    GpsrService::MAPPING_TYPE,
                    $newPair->channelAttributeTitle,
                    $newPair->channelAttributeCode,
                    $newPair->magentoAttributeCode
                );
            } else {
                $exists[] =
                    $this->getAdapter()->createPair(
                        GpsrService::MAPPING_TYPE,
                        $newPair->channelAttributeTitle,
                        $newPair->channelAttributeCode,
                        $newPair->magentoAttributeCode
                    );
            }

            unset($existedByAttributeCode[$newPair->channelAttributeCode]);
        }

        $processedCountCreate = $this->getAdapter()->create($new, GpsrService::MAPPING_TYPE);
        $processedCountCreateUpdate = $this->getAdapter()->update($exists, GpsrService::MAPPING_TYPE);

        if (!empty($existedByAttributeCode)) {
            $this->getAdapter()->removeByChannelCodes($existedByAttributeCode);
        }

        return $processedCountCreate + $processedCountCreateUpdate;
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
     * @return \M2E\Core\Model\AttributeMapping\Pair[]
     */
    private function getExistedMappingGroupedByCode(): array
    {
        $result = [];

        $existed = $this->getAdapter()->findByType(GpsrService::MAPPING_TYPE);
        foreach ($existed as $pair) {
            $result[$pair->getChannelAttributeCode()] = $pair;
        }

        return $result;
    }

    private function getAdapter(): \M2E\Core\Model\AttributeMapping\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->attributeMappingAdapter)) {
            $this->attributeMappingAdapter = $this->attributeMappingAdapterFactory->create(
                \M2E\Otto\Helper\Module::IDENTIFIER
            );
        }

        return $this->attributeMappingAdapter;
    }
}
