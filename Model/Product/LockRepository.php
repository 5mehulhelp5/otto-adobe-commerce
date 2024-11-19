<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

class LockRepository
{
    private \M2E\Otto\Model\ResourceModel\Product\Lock $resource;
    private \M2E\Otto\Model\ResourceModel\Product\Lock\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product\Lock $resource,
        \M2E\Otto\Model\ResourceModel\Product\Lock\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    public function create(Lock $lock): void
    {
        $this->resource->save($lock);
    }

    public function findById(int $lockId): ?Lock
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\Otto\Model\ResourceModel\Product\Lock::COLUMN_ID, $lockId);

        /** @var Lock $item */
        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function findByProductId(int $productId): ?Lock
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\Otto\Model\ResourceModel\Product\Lock::COLUMN_PRODUCT_ID, $productId);

        /** @var Lock $item */
        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function remove(Lock $lock): void
    {
        $this->resource->delete($lock);
    }
}
