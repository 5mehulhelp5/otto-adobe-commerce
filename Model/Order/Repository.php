<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Order $orderResource;
    private \M2E\Otto\Model\OrderFactory $orderFactory;
    private \M2E\Otto\Model\ResourceModel\Order\Item $orderItemResource;
    /** @var \M2E\Otto\Model\Order\ItemFactory */
    private ItemFactory $itemFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Order $orderResource,
        \M2E\Otto\Model\OrderFactory $orderFactory,
        \M2E\Otto\Model\ResourceModel\Order\Item $orderItemResource,
        \M2E\Otto\Model\Order\ItemFactory $itemFactory,
        \M2E\Otto\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory
    ) {
        $this->orderItemResource = $orderItemResource;
        $this->itemFactory = $itemFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderChangeCollectionFactory = $orderChangeCollectionFactory;
        $this->orderNoteCollectionFactory = $orderNoteCollectionFactory;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
    }

    public function get(int $id): \M2E\Otto\Model\Order
    {
        $order = $this->find($id);
        if ($order === null) {
            throw new \M2E\Otto\Model\Exception\Logic("Order $id not found.");
        }

        return $order;
    }

    public function find(int $id): ?\M2E\Otto\Model\Order
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $id);

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function findByMagentoOrderId(int $id): ?\M2E\Otto\Model\Order
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $id, \M2E\Otto\Model\ResourceModel\Order::COLUMN_MAGENTO_ORDER_ID);

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function removeByAccountId(int $accountId): void
    {
        $this->removeRelatedOrderChangesByAccountId($accountId);
        $this->removeRelatedOrderItemsByAccountId($accountId);
        $this->removeRelatedOrderNoteByAccountId($accountId);

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->getConnection()->delete(
            $orderCollection->getMainTable(),
            ['account_id = ?' => $accountId]
        );
    }

    private function removeRelatedOrderItemsByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            \M2E\Otto\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderItemCollection = $this->orderItemCollectionFactory->create();
        $orderItemCollection->getConnection()->delete(
            $orderItemCollection->getMainTable(),
            [
                \M2E\Otto\Model\ResourceModel\Order\Item::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    private function removeRelatedOrderChangesByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            \M2E\Otto\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderChangeCollection = $this->orderChangeCollectionFactory->create();
        $orderChangeCollection->getConnection()->delete(
            $orderChangeCollection->getMainTable(),
            [
                \M2E\Otto\Model\ResourceModel\Order\Change::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    private function removeRelatedOrderNoteByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            \M2E\Otto\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderNoteCollection = $this->orderNoteCollectionFactory->create();
        $orderNoteCollection->getConnection()->delete(
            $orderNoteCollection->getMainTable(),
            [
                \M2E\Otto\Model\ResourceModel\Order\Note::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    /**
     * @param \M2E\Otto\Model\Order $order
     *
     * @return \M2E\Otto\Model\Order\Item[]
     */
    public function findItemsByOrder(\M2E\Otto\Model\Order $order): array
    {
        $collection = $this->orderItemCollectionFactory->create();
        $collection
            ->addFieldToFilter(\M2E\Otto\Model\ResourceModel\Order\Item::COLUMN_ORDER_ID, (int)$order->getId());

        $result = [];
        foreach ($collection->getItems() as $item) {
            $result[] = $item->setOrder($order);
        }

        return $result;
    }

    public function findItemById(int $id): ?\M2E\Otto\Model\Order\Item
    {
        $orderItem = $this->itemFactory->create();
        $this->orderItemResource->load($orderItem, $id);

        if ($orderItem->isObjectNew()) {
            return null;
        }

        return $orderItem;
    }
}
