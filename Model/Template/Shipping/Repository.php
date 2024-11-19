<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping;

use M2E\Otto\Model\ResourceModel\Template\Shipping as ShippingResource;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Template\Shipping $resource;
    private \M2E\Otto\Model\ResourceModel\Template\Shipping\CollectionFactory $collectionFactory;
    private \M2E\Otto\Model\Template\ShippingFactory $shippingFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Template\Shipping $resource,
        \M2E\Otto\Model\ResourceModel\Template\Shipping\CollectionFactory $collectionFactory,
        \M2E\Otto\Model\Template\ShippingFactory $shippingFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->shippingFactory = $shippingFactory;
    }

    public function find(int $id): ?\M2E\Otto\Model\Template\Shipping
    {
        $model = $this->shippingFactory->createEmpty();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function get(int $id): \M2E\Otto\Model\Template\Shipping
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\Otto\Model\Exception\Logic('Shipping not found');
        }

        return $template;
    }

    public function create(\M2E\Otto\Model\Template\Shipping $template): void
    {
        $this->resource->save($template);
    }

    public function save(\M2E\Otto\Model\Template\Shipping $template): void
    {
        $this->resource->save($template);
    }

    public function delete(\M2E\Otto\Model\Template\Shipping $template)
    {
        $template->delete();
    }

    /**
     * @return \M2E\Otto\Model\Template\Shipping[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    public function findOldPoliciesByTitle(string $title): ?\M2E\Otto\Model\Template\Shipping
    {
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(ShippingResource::COLUMN_TITLE, ['eq' => $title]);
        $collection->addFieldToFilter(ShippingResource::COLUMN_SHIPPING_PROFILE_ID, ['null' => true]);

        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function findByAccount(int $accountId): ShippingCollection
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ShippingResource::COLUMN_ACCOUNT_ID, $accountId);

        $result = new ShippingCollection();
        foreach ($collection->getItems() as $item) {
            $result->add($item);
        }

        return $result;
    }
}
