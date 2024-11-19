<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

class Configurator
{
    private const MODE_INCLUDING = 'including';
    private const MODE_EXCLUDING = 'excluding';

    public const DATA_TYPE_GENERAL = 'general';
    public const DATA_TYPE_QTY = 'qty';
    public const DATA_TYPE_PRICE = 'price';
    public const DATA_TYPE_TITLE = 'title';
    public const DATA_TYPE_DESCRIPTION = 'description';
    public const DATA_TYPE_IMAGES = 'images';
    public const DATA_TYPE_CATEGORIES = 'categories';
    public const DATA_TYPE_SHIPPING_PROFILE = 'shipping_profile';

    private static array $allTypes = [
        self::DATA_TYPE_GENERAL,
        self::DATA_TYPE_QTY,
        self::DATA_TYPE_PRICE,
        self::DATA_TYPE_TITLE,
        self::DATA_TYPE_DESCRIPTION,
        self::DATA_TYPE_IMAGES,
        self::DATA_TYPE_CATEGORIES,
        self::DATA_TYPE_SHIPPING_PROFILE
    ];

    private string $mode = self::MODE_EXCLUDING;
    private array $allowedDataTypes;
    private array $params = [];

    public function __construct()
    {
        $this->allowedDataTypes = $this->getAllDataTypes();
    }

    public function getAllDataTypes(): array
    {
        return [
            self::DATA_TYPE_GENERAL,
            self::DATA_TYPE_QTY,
            self::DATA_TYPE_PRICE,
            self::DATA_TYPE_TITLE,
            self::DATA_TYPE_DESCRIPTION,
            self::DATA_TYPE_IMAGES,
            self::DATA_TYPE_CATEGORIES,
            self::DATA_TYPE_SHIPPING_PROFILE
        ];
    }

    public static function createWithTypes(array $types): self
    {
        $allowedDataTypes = [];
        foreach ($types as $type) {
            if (!in_array($type, self::$allTypes)) {
                continue;
            }

            $allowedDataTypes[] = $type;
        }

        $configurator = new self();
        $configurator->disableAll();

        $configurator->allowedDataTypes = $allowedDataTypes;

        return $configurator;
    }

    public function enableAll(): self
    {
        $this->mode = self::MODE_EXCLUDING;
        $this->allowedDataTypes = self::$allTypes;

        return $this;
    }

    public function disableAll(): self
    {
        $this->mode = self::MODE_INCLUDING;
        $this->allowedDataTypes = [];

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function isExcludingMode(): bool
    {
        return $this->mode == self::MODE_EXCLUDING;
    }

    public function getAllowedDataTypes(): array
    {
        return $this->allowedDataTypes;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isAllowed(string $dataType): bool
    {
        $this->validateDataType($dataType);

        return in_array($dataType, $this->allowedDataTypes);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function allow($dataType): self
    {
        $this->validateDataType($dataType);

        if (!in_array($dataType, $this->allowedDataTypes)) {
            $this->allowedDataTypes[] = $dataType;
        }

        return $this;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function disallow($dataType): self
    {
        $this->validateDataType($dataType);

        if (in_array($dataType, $this->allowedDataTypes)) {
            $this->allowedDataTypes = array_diff($this->allowedDataTypes, [$dataType]);
        }

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    // ---------------------------------------

    public function getSerializedData(): array
    {
        return [
            'mode' => $this->mode,
            'allowed_data_types' => $this->allowedDataTypes,
            'params' => $this->params,
        ];
    }

    /**
     * @param array $data
     *
     * @return $this
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function setUnserializedData(array $data): self
    {
        if (!empty($data['mode'])) {
            $this->mode = $data['mode'];
        }

        if (!empty($data['allowed_data_types'])) {
            if (
                !is_array($data['allowed_data_types']) ||
                array_diff($data['allowed_data_types'], $this->getAllDataTypes())
            ) {
                throw new \M2E\Otto\Model\Exception\Logic(
                    'Allowed data types are invalid.',
                    ['allowed_data_types' => $data['allowed_data_types']]
                );
            }

            $this->allowedDataTypes = $data['allowed_data_types'];
        }

        if (!empty($data['params'])) {
            if (!is_array($data['params'])) {
                throw new \InvalidArgumentException('Params has invalid format.');
            }

            $this->params = $data['params'];
        }

        return $this;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    protected function validateDataType($dataType)
    {
        if (!in_array($dataType, $this->getAllDataTypes())) {
            throw new \M2E\Otto\Model\Exception\Logic(
                'Data type is invalid',
                ['data_type' => $dataType]
            );
        }
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isQtyAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_QTY);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function allowQty(): self
    {
        return $this->allow(self::DATA_TYPE_QTY);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function disallowQty(): self
    {
        return $this->disallow(self::DATA_TYPE_QTY);
    }

    // ---------------------------------------

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isPriceAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_PRICE);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function allowPrice(): self
    {
        return $this->allow(self::DATA_TYPE_PRICE);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function disallowPrice(): self
    {
        return $this->disallow(self::DATA_TYPE_PRICE);
    }

    // ---------------------------------------

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isTitleAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_TITLE);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function allowTitle(): self
    {
        return $this->allow(self::DATA_TYPE_TITLE);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function disallowTitle(): self
    {
        return $this->disallow(self::DATA_TYPE_TITLE);
    }

    // ---------------------------------------

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isDescriptionAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_DESCRIPTION);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function allowDescription(): self
    {
        return $this->allow(self::DATA_TYPE_DESCRIPTION);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function disallowDescription(): self
    {
        return $this->disallow(self::DATA_TYPE_DESCRIPTION);
    }

    // ---------------------------------------

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isImagesAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_IMAGES);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function allowImages(): self
    {
        return $this->allow(self::DATA_TYPE_IMAGES);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function disallowImages(): self
    {
        return $this->disallow(self::DATA_TYPE_IMAGES);
    }

    // ---------------------------------------

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isCategoriesAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_CATEGORIES);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function allowCategories(): Configurator
    {
        return $this->allow(self::DATA_TYPE_CATEGORIES);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function disallowCategories(): self
    {
        return $this->disallow(self::DATA_TYPE_CATEGORIES);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isShippingProfileAllowed(): bool
    {
        return $this->isAllowed(self::DATA_TYPE_SHIPPING_PROFILE);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function allowShippingProfile(): Configurator
    {
        return $this->allow(self::DATA_TYPE_SHIPPING_PROFILE);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function disallowShippingProfile(): self
    {
        return $this->disallow(self::DATA_TYPE_SHIPPING_PROFILE);
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function isDetailsAllowed(): bool
    {
        return $this->isTitleAllowed()
            || $this->isDescriptionAllowed()
            || $this->isImagesAllowed()
            || $this->isCategoriesAllowed()
            || $this->isShippingProfileAllowed();
    }
}
