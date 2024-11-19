<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Brand;

use M2E\Otto\Model\ResourceModel\Brand as BrandResource;

class Repository
{
    private BrandResource\CollectionFactory $brandCollectionFactory;

    public function __construct(
        BrandResource\CollectionFactory $brandCollectionFactory
    ) {
        $this->brandCollectionFactory = $brandCollectionFactory;
    }

    /**
     * @return \M2E\Otto\Model\Brand[]
     */
    public function findByBrandNames(array $names): array
    {
        $collection = $this->brandCollectionFactory->create();

        $collection->addFieldToFilter(
            BrandResource::COLUMN_NAME,
            ['in' => $names]
        );

        return array_values($collection->getItems());
    }

    /**
     * @param \M2E\Otto\Model\Brand[] $brands
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function batchInsert(array $brands): void
    {
        $insertData = [];
        foreach ($brands as $brand) {
            $insertData[] = [
                BrandResource::COLUMN_BRAND_ID => $brand->getBrandId(),
                BrandResource::COLUMN_NAME => $brand->getName(),
                BrandResource::COLUMN_IS_USABLE => $brand->getIsUsable()
            ];
        }

        $collection = $this->brandCollectionFactory->create();
        $resource = $collection->getResource();

        foreach (array_chunk($insertData, 500) as $chunk) {
            $resource->getConnection()->insertMultiple($resource->getMainTable(), $chunk);
        }
    }
}
