<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other;

use M2E\Otto\Model\ResourceModel\Listing\Other as ListingOtherResource;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory;
    private \M2E\Otto\Model\ResourceModel\Listing\Other $resource;
    private \M2E\Otto\Model\Listing\OtherFactory $objectFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory,
        \M2E\Otto\Model\ResourceModel\Listing\Other $resource,
        \M2E\Otto\Model\Listing\OtherFactory $objectFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->objectFactory = $objectFactory;
    }

    public function createCollection(): \M2E\Otto\Model\ResourceModel\Listing\Other\Collection
    {
        return $this->collectionFactory->create();
    }

    public function create(\M2E\Otto\Model\Listing\Other $other): void
    {
        $this->resource->save($other);
    }

    public function save(\M2E\Otto\Model\Listing\Other $listingOther): void
    {
        $this->resource->save($listingOther);
    }

    /**
     * @throws \M2E\Otto\Model\Exception
     */
    public function get(int $id): \M2E\Otto\Model\Listing\Other
    {
        $obj = $this->objectFactory->create();
        $this->resource->load($obj, $id);

        if ($obj->isObjectNew()) {
            throw new \M2E\Otto\Model\Exception("Object by id $id not found.");
        }

        return $obj;
    }

    public function remove(\M2E\Otto\Model\Listing\Other $other): void
    {
        $this->resource->delete($other);
    }

    /**
     * @param int $id
     *
     * @return \M2E\Otto\Model\Listing\Other|null
     */
    public function findById(int $id): ?\M2E\Otto\Model\Listing\Other
    {
        $obj = $this->objectFactory->create();
        $this->resource->load($obj, $id);

        if ($obj->isObjectNew()) {
            return null;
        }

        return $obj;
    }

    /**
     * @return \M2E\Otto\Model\Listing\Other[]
     */
    public function findByIds(array $ids): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_ID,
            ['in' => $ids],
        );

        return array_values($collection->getItems());
    }

    /**
     * @param array $productsSKUs
     * @param int $accountId
     *
     * @return \M2E\Otto\Model\Listing\Other[]
     */
    public function findByProductSKUs(array $productsSKUs, int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ListingOtherResource::COLUMN_SKU,
                ['in' => $productsSKUs],
            )
            ->addFieldToFilter(ListingOtherResource::COLUMN_ACCOUNT_ID, $accountId);

        return array_values($collection->getItems());
    }

    public function findByProductEANs(array $productsEANs, int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ListingOtherResource::COLUMN_EAN,
                ['in' => $productsEANs],
            )
            ->addFieldToFilter(ListingOtherResource::COLUMN_ACCOUNT_ID, $accountId);

        return array_values($collection->getItems());
    }

    public function removeByAccountId(int $accountId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            ['account_id = ?' => $accountId],
        );
    }

    public function getBySku(string $sku): ?\M2E\Otto\Model\Listing\Other
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('sku', $sku);

        $item = $collection->getFirstItem();
        if (!$item->getId()) {
            return null;
        }

        return $item;
    }

    public function findByMagentoProductId(int $magentoProductId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return array_values($collection->getItems());
    }

    public function isExistForAccountId(int $accountId): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_ACCOUNT_ID, $accountId);

        return (int)$collection->getSize() > 0;
    }
}
