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

    private \M2E\Otto\Model\AttributeMapping\Repository $attributeMappingRepository;

    public function __construct(\M2E\Otto\Model\AttributeMapping\Repository $attributeMappingRepository)
    {
        $this->attributeMappingRepository = $attributeMappingRepository;
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
}
