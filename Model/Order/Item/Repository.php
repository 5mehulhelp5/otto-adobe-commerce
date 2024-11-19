<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Item;

use M2E\Otto\Model\ResourceModel\Order\Item as OrderItem;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Order\Item $orderItemResource;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Order\Item $orderItemResource
    ) {
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderItemResource = $orderItemResource;
    }

    /**
     * @param \M2E\Otto\Model\Order\Item $orderItem
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\M2E\Otto\Model\Order\Item $orderItem): void
    {
        $this->orderItemResource->save($orderItem);
    }

    /**
     * @param array $orderItemIds
     *
     * @return \M2E\Otto\Model\Order\Item[]
     */
    public function findOrderItemsByIds(array $orderItemIds): array
    {
        $itemsCollection = $this->orderItemCollectionFactory->create();
        $itemsCollection->addFieldToFilter(OrderItem::COLUMN_ID, ['in' => $orderItemIds]);

        return array_values($itemsCollection->getItems());
    }

    /**
     * @param int $orderId
     *
     * @return \M2E\Otto\Model\ResourceModel\Order\Item\Collection
     */
    public function getGroupOrderItems(int $orderId): OrderItem\Collection
    {
        $collection = $this->orderItemCollectionFactory->create();
        $collection->addFieldToFilter(OrderItem::COLUMN_ORDER_ID, $orderId);

        $collection->getSelect()->group(OrderItem::COLUMN_OTTO_PRODUCT_SKU);
        $collection->getSelect()->columns(
            [
                'total_qty' => new \Zend_Db_Expr(
                    sprintf('SUM(%s)', OrderItem::COLUMN_QTY_PURCHASED)
                )
            ]
        );
        $collection->getSelect()->columns(
            [
                'order_items_ids' => new \Zend_Db_Expr(
                    sprintf('GROUP_CONCAT(%s SEPARATOR ", ")', OrderItem::COLUMN_ID)
                )
            ]
        );

        return $collection;
    }
}
