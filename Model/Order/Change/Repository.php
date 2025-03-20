<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Change;

use M2E\Otto\Model\ResourceModel\Order\Change as ChangeResource;

class Repository
{
    private ChangeResource $changeResource;
    private \M2E\Otto\Model\ResourceModel\Order\Change\CollectionFactory $collectionFactory;
    private \M2E\Otto\Model\Order\ChangeFactory $changeFactory;
    private \M2E\Otto\Model\ResourceModel\Order $orderResource;

    public function __construct(
        \M2E\Otto\Model\Order\ChangeFactory $changeFactory,
        \M2E\Otto\Model\ResourceModel\Order\Change $changeResource,
        \M2E\Otto\Model\ResourceModel\Order\Change\CollectionFactory $collectionFactory,
        \M2E\Otto\Model\ResourceModel\Order $orderResource
    ) {
        $this->changeFactory = $changeFactory;
        $this->orderResource = $orderResource;
        $this->changeResource = $changeResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function create(\M2E\Otto\Model\Order\Change $change): void
    {
        $this->changeResource->save($change);
    }

    public function save(\M2E\Otto\Model\Order\Change $change): void
    {
        $this->changeResource->save($change);
    }

    /**
     * @param int $orderId
     * @param int $magentoShipmentId
     *
     * @return \M2E\Otto\Model\Order\Change[]
     */
    public function findWithActionShippingByOrder(int $orderId, int $magentoShipmentId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(ChangeResource::COLUMN_ORDER_ID, $orderId)
            ->addFieldToFilter(ChangeResource::COLUMN_MAGENTO_SHIPMENT_ID, $magentoShipmentId)
            ->addFieldToFilter(
                ChangeResource::COLUMN_ACTION,
                \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING,
            );

        return array_values($collection->getItems());
    }

    public function createShippingOrUpdateNotProcessed(
        \M2E\Otto\Model\Order $order,
        array $params,
        ?int $initiator,
        int $magentoShipmentId
    ): void {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ChangeResource::COLUMN_ORDER_ID, $order->getId())
                   ->addFieldToFilter(ChangeResource::COLUMN_MAGENTO_SHIPMENT_ID, $magentoShipmentId)
                   ->addFieldToFilter(
                       ChangeResource::COLUMN_ACTION,
                       \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING,
                   )
                   ->addFieldToFilter(ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT, 0);

        $change = $collection->getFirstItem();
        if ($change->isObjectNew()) {
            $change = $this->changeFactory->create();
            $change->init(
                $order->getId(),
                $magentoShipmentId,
                \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING,
                $initiator,
                $params,
                $this->generateHash($order->getId(), \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING, $params),
            );

            $this->changeResource->save($change);

            return;
        }

        $change->setParams($params);

        $this->changeResource->save($change);
    }

    private function generateHash($orderId, $action, array $params): string
    {
        return sha1($orderId . '-' . $action . '-' . json_encode($params, JSON_THROW_ON_ERROR));
    }

    /**
     * @param \M2E\Otto\Model\Account $account
     * @param int $limit
     *
     * @return \M2E\Otto\Model\Order\Change[]
     */
    public function findShippingForProcess(\M2E\Otto\Model\Account $account, int $limit): array
    {
        $collection = $this->collectionFactory->create();

        $collection->getSelect()
                   ->join(
                       ['mo' => $this->orderResource->getMainTable()],
                       sprintf(
                           '(`mo`.`id` = `main_table`.`%s` AND `mo`.`%s` = %s)',
                           ChangeResource::COLUMN_ORDER_ID,
                           \M2E\Otto\Model\ResourceModel\Order::COLUMN_ACCOUNT_ID,
                           $account->getId(),
                       ),
                       ['account_id'],
                   );

        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $currentDate->modify('-3600 seconds');

        $collection->getSelect()
                   ->where(
                       sprintf(
                           '%s = 0 OR %s <= ?',
                           ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT,
                           ChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
                       ),
                       $currentDate->format('Y-m-d H:i:s'),
                   );
        $collection->addFieldToFilter(
            ChangeResource::COLUMN_ACTION,
            ['eq' => \M2E\Otto\Model\Order\Change::ACTION_UPDATE_SHIPPING],
        );
        $collection->setPageSize($limit);
        $collection->setOrder(
            sprintf('main_table.%s', ChangeResource::COLUMN_CREATE_DATE),
            \Magento\Framework\Data\Collection::SORT_ORDER_ASC,
        );
        $collection->getSelect()
                   ->group([ChangeResource::COLUMN_ORDER_ID]);

        return array_values($collection->getItems());
    }

    public function remove(\M2E\Otto\Model\Order\Change $change): void
    {
        $this->changeResource->delete($change);
    }

    public function deleteByProcessingAttemptCount(int $count): void
    {
        $where = [
            sprintf('%s >= ?', ChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT) => $count,
        ];

        $this->changeResource
            ->getConnection()->delete(
                $this->changeResource->getMainTable(),
                $where,
            );
    }
}
