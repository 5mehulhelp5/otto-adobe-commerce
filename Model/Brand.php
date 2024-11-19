<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\ResourceModel\Brand as BrandResource;

class Brand extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(BrandResource::class);
    }

    public function init(
        string $name,
        string $brandId,
        bool $isUsable
    ): Brand {
        $this
            ->setData(BrandResource::COLUMN_NAME, $name)
            ->setData(BrandResource::COLUMN_BRAND_ID, $brandId)
            ->setData(BrandResource::COLUMN_IS_USABLE, $isUsable);

        return $this;
    }

    public function getName(): string
    {
        return $this->getData(BrandResource::COLUMN_NAME);
    }

    public function getBrandId(): string
    {
        return $this->getData(BrandResource::COLUMN_BRAND_ID);
    }

    public function getIsUsable(): bool
    {
        return (bool)$this->getData(BrandResource::COLUMN_IS_USABLE);
    }
}
