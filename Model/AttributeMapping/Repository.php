<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AttributeMapping;

use M2E\Otto\Model\ResourceModel\AttributeMapping\Pair as PairResource;

class Repository
{
    private PairResource $resource;
    private \M2E\Otto\Model\ResourceModel\AttributeMapping\Pair\CollectionFactory $collectionFactory;

    public function __construct(
        PairResource $resource,
        \M2E\Otto\Model\ResourceModel\AttributeMapping\Pair\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    public function create(Pair $pair): void
    {
        $this->resource->save($pair);
    }

    public function save(Pair $pair): void
    {
        $this->resource->save($pair);
    }

    public function remove(Pair $pair): void
    {
        $this->resource->delete($pair);
    }

    /**
     * @param string $type
     *
     * @return Pair[]
     */
    public function findByType(string $type): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PairResource::COLUMN_TYPE, ['eq' => $type]);

        return array_values($collection->getItems());
    }
}
