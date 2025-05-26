<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AttributeMapping\Gpsr;

class Provider
{
    private const ATTRIBUTES = [
        [
            'title' => 'Herstellername',
            'code' => 'product_safety_name',
        ],
        [
            'title' => 'Herstelleradresse',
            'code' => 'product_safety_address',
        ],
        [
            'title' => 'Hersteller-E-Mail-Adresse',
            'code' => 'product_safety_email',
        ],
        [
            'title' => 'Hersteller Telefon',
            'code' => 'product_safety_phone',
        ],
        [
            'title' => 'Hersteller-Regionalcode',
            'code' => 'product_safety_region_code',
        ],
        [
            'title' => 'Hersteller-URL',
            'code' => 'product_safety_url',
        ],
    ];

    private \M2E\Core\Model\AttributeMapping\Adapter $attributeMappingAdapter;
    private \M2E\Core\Model\AttributeMapping\AdapterFactory $attributeMappingAdapterFactory;

    public function __construct(
        \M2E\Core\Model\AttributeMapping\AdapterFactory $attributeMappingAdapterFactory
    ) {
        $this->attributeMappingAdapterFactory = $attributeMappingAdapterFactory;
    }

    /**
     * @return \M2E\Otto\Model\AttributeMapping\Gpsr\Pair[]
     */
    public function getAll(): array
    {
        return $this->retrieve(false);
    }

    /**
     * @return \M2E\Otto\Model\AttributeMapping\Gpsr\Pair[]
     */
    public function getConfigured(): array
    {
        return $this->retrieve(true);
    }

    /**
     * @return \M2E\Otto\Model\AttributeMapping\Gpsr\Pair[]
     */
    private function retrieve(bool $onlyConfigured): array
    {
        $existedByCode = $this->getExistedMappingGroupedByCode();

        $result = [];
        foreach (self::ATTRIBUTES as ['title' => $channelTitle, 'code' => $channelCode]) {
            $mappingId = null;
            $magentoAttributeCode = null;
            if (isset($existedByCode[$channelCode])) {
                $mappingId = $existedByCode[$channelCode]->getId();
                $magentoAttributeCode = $existedByCode[$channelCode]->getMagentoAttributeCode();
            }

            if (
                $mappingId === null
                && $onlyConfigured
            ) {
                continue;
            }

            $result[] = new \M2E\Otto\Model\AttributeMapping\Gpsr\Pair(
                $mappingId,
                \M2E\Otto\Model\AttributeMapping\GpsrService::MAPPING_TYPE,
                $channelTitle,
                $channelCode,
                $magentoAttributeCode
            );
        }

        return $result;
    }

    /**
     * @return \M2E\Core\Model\AttributeMapping\Pair[]
     */
    private function getExistedMappingGroupedByCode(): array
    {
        $result = [];

        $existed = $this->getAdapter()->findByType(\M2E\Otto\Model\AttributeMapping\GpsrService::MAPPING_TYPE);
        foreach ($existed as $pair) {
            $result[$pair->getChannelAttributeCode()] = $pair;
        }

        return $result;
    }

    /**
     * @return \M2E\Core\Model\AttributeMapping\Pair[]
     */
    public function getExistedMappingGroupedByTitle(): array
    {
        $result = [];

        $existed = $this->getAdapter()->findByType(\M2E\Otto\Model\AttributeMapping\GpsrService::MAPPING_TYPE);
        foreach ($existed as $pair) {
            $result[$pair->getChannelAttributeTitle()] = $pair;
        }

        return $result;
    }

    // ----------------------------------------

    /**
     * @return string[]
     */
    public static function getAllAttributesCodes(): array
    {
        return array_column(self::ATTRIBUTES, 'code');
    }

    public static function getAttributeTitle(string $code): ?string
    {
        foreach (self::ATTRIBUTES as ['title' => $channelTitle, 'code' => $channelCode]) {
            if ($code !== $channelCode) {
                continue;
            }

            return $channelTitle;
        }

        return null;
    }

    public function isGpsrAttribute(string $title): bool
    {
        foreach (self::ATTRIBUTES as $attribute) {
            if ($attribute['title'] === $title) {
                return true;
            }
        }

        return false;
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
