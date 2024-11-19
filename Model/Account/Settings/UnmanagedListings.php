<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Account\Settings;

class UnmanagedListings
{
    public const MAPPING_TYPE_BY_SKU = 'sku';
    public const MAPPING_TYPE_BY_EAN = 'ean';
    public const MAPPING_TYPE_BY_TITLE = 'title';

    public const MAPPING_TITLE_MODE_NONE = 0;
    public const MAPPING_TITLE_MODE_DEFAULT = 1;
    public const MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE = 2;

    public const MAPPING_SKU_MODE_NONE = 0;
    public const MAPPING_SKU_MODE_DEFAULT = 1;
    public const MAPPING_SKU_MODE_PRODUCT_ID = 2;
    public const MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE = 3;

    public const MAPPING_EAN_MODE_NONE = 0;
    public const MAPPING_EAN_MODE_CUSTOM_ATTRIBUTE = 1;

    private bool $isSyncEnabled = true;
    private bool $isMappingEnabled = true;
    private int $relatedStoreId = 0;
    private array $mappingBySku = [
        'mode' => self::MAPPING_SKU_MODE_DEFAULT,
        'priority' => 1,
        'attribute' => null,
    ];
    private array $mappingByEan = [
        'mode' => 0,
        'priority' => 2,
        'attribute' => null,
    ];
    private array $mappingByTitle = [
        'mode' => 0,
        'priority' => 3,
        'attribute' => null,
    ];

    private array $mappingTypesByPriority;

    public function isSyncEnabled(): bool
    {
        return $this->isSyncEnabled;
    }

    public function createWithSync(bool $status): self
    {
        $new = clone $this;
        $new->isSyncEnabled = $status;

        return $new;
    }

    public function isMappingEnabled(): bool
    {
        return $this->isMappingEnabled;
    }

    public function createWithMapping(bool $status): self
    {
        $new = clone $this;
        $new->isMappingEnabled = $status;

        return $new;
    }

    // ----------------------------------------

    /**
     * @return string[] MAPPING_TYPE_* const
     */
    public function getMappingTypesByPriority(): array
    {
        if (!$this->isMappingEnabled) {
            return [];
        }

        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->mappingTypesByPriority)) {
            return $this->mappingTypesByPriority;
        }

        $types = [];
        if ($this->isMappingBySkuEnabled()) {
            $types[self::MAPPING_TYPE_BY_SKU] = $this->getPriorityForMappingBySku();
        }

        if ($this->isMappingByEanEnabled()) {
            $types[self::MAPPING_TYPE_BY_EAN] = $this->getPriorityForMappingByEan();
        }

        if ($this->isMappingByTitleEnabled()) {
            $types[self::MAPPING_TYPE_BY_TITLE] = $this->getPriorityForMappingByTitle();
        }

        asort($types, SORT_NUMERIC);

        return $this->mappingTypesByPriority = array_keys($types);
    }

    public function getMappingBySkuMode(): int
    {
        return $this->mappingBySku['mode'];
    }

    public function isMappingBySkuEnabled(): bool
    {
        return $this->isMappingBySkuModeBySku()
            || $this->isMappingBySkuModeByProductId()
            || $this->isMappingBySkuModeByAttribute();
    }

    public function isMappingBySkuModeBySku(): bool
    {
        return $this->mappingBySku['mode'] === self::MAPPING_SKU_MODE_DEFAULT;
    }

    public function isMappingBySkuModeByProductId(): bool
    {
        return $this->mappingBySku['mode'] === self::MAPPING_SKU_MODE_PRODUCT_ID;
    }

    public function isMappingBySkuModeByAttribute(): bool
    {
        return $this->mappingBySku['mode'] === self::MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getMappingAttributeBySku(): ?string
    {
        return $this->isMappingBySkuModeByAttribute()
            ? $this->mappingBySku['attribute'] : null;
    }

    public function getPriorityForMappingBySku(): int
    {
        return $this->mappingBySku['priority'];
    }

    public function getMappingByTitleMode(): int
    {
        return $this->mappingByTitle['mode'];
    }

    public function isMappingByTitleEnabled(): bool
    {
        return $this->isMappingByTitleModeByProductName()
            || $this->isMappingByTitleModeByAttribute();
    }

    public function isMappingByTitleModeByProductName(): bool
    {
        return $this->mappingByTitle['mode'] === self::MAPPING_TITLE_MODE_DEFAULT;
    }

    public function isMappingByTitleModeByAttribute(): bool
    {
        return $this->mappingByTitle['mode'] === self::MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getPriorityForMappingByTitle(): int
    {
        return $this->mappingByTitle['priority'];
    }

    public function getMappingAttributeByTitle(): ?string
    {
        return $this->isMappingByTitleModeByAttribute()
            ? $this->mappingByTitle['attribute'] : null;
    }

    public function getMappingByEanMode(): int
    {
        return $this->mappingByEan['mode'];
    }

    public function isMappingByEanEnabled(): bool
    {
        return $this->isMappingByEanModeByAttribute();
    }

    public function isMappingByEanModeByAttribute(): bool
    {
        return $this->mappingByEan['mode'] === self::MAPPING_EAN_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getPriorityForMappingByEan(): int
    {
        return $this->mappingByEan['priority'];
    }

    public function getMappingAttributeByEan(): ?string
    {
        return $this->isMappingByEanModeByAttribute()
            ? $this->mappingByEan['attribute'] : null;
    }

    public function createWithMappingSettings(
        array $bySku,
        array $byEan,
        array $byTitle
    ): self {
        $new = clone $this;
        if (!empty($bySku)) {
            $new->mappingBySku = array_merge($new->mappingBySku, $this->prepareData($bySku));
        }

        if (!empty($byEan)) {
            $new->mappingByEan = array_merge($new->mappingByEan, $this->prepareData($byEan));
        }

        if (!empty($byTitle)) {
            $new->mappingByTitle = array_merge($new->mappingByTitle, $this->prepareData($byTitle));
        }

        unset($new->mappingTypesByPriority);

        return $new;
    }

    public function getMappingBySkuSettings(): array
    {
        return $this->mappingBySku;
    }

    public function getMappingByTitleSettings(): array
    {
        return $this->mappingByTitle;
    }

    public function getMappingByEanSettings(): array
    {
        return $this->mappingByEan;
    }

    public function getRelatedStoreId(): int
    {
        return $this->relatedStoreId;
    }

    public function createWithRelatedStoreId(int $storeId): self
    {
        $new = clone $this;
        $new->relatedStoreId = $storeId;

        return $new;
    }

    private function prepareData(array $mappingData): array
    {
        if (isset($mappingData['mode'])) {
            $mappingData['mode'] = (int)$mappingData['mode'];
        }

        if (isset($mappingData['priority'])) {
            $mappingData['priority'] = (int)$mappingData['priority'];
        }

        if (isset($mappingData['attribute'])) {
            $mappingData['attribute'] = empty($mappingData['attribute']) ? null : $mappingData['attribute'];
        }

        return $mappingData;
    }
}
