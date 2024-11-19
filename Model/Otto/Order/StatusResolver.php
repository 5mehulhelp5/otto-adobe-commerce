<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Order;

class StatusResolver
{
    private const ORDER_STATUS_ANNOUNCED = 'announced';
    private const ORDER_STATUS_PROCESSABLE = 'processable';
    private const ORDER_STATUS_SENT = 'sent';
    private const ORDER_STATUS_RETURNED = 'returned';
    private const ORDER_STATUS_CANCELLED_BY_PARTNER = 'cancelled_by_partner';
    private const ORDER_STATUS_CANCELLED_BY_MARKETPLACE = 'cancelled_by_marketplace';

    public function resolveByOrderItems(array $items)
    {
        $orderStatuses = [];
        foreach ($items as $item) {
            $orderStatuses[] = $this->convertOttoOrderStatus($item['fulfillment_status']);
        }

        if (count(array_unique($orderStatuses)) === 1) {
            return reset($orderStatuses);
        }

        if (in_array(\M2E\Otto\Model\Order::STATUS_RETURNED, $orderStatuses)) {
            return \M2E\Otto\Model\Order::STATUS_RETURNED_PARTIALLY;
        }

        if (in_array(\M2E\Otto\Model\Order::STATUS_CANCELED, $orderStatuses)) {
            return \M2E\Otto\Model\Order::STATUS_CANCELED_PARTIALLY;
        }

        if (in_array(\M2E\Otto\Model\Order::STATUS_SHIPPED, $orderStatuses)) {
            return \M2E\Otto\Model\Order::STATUS_SHIPPED_PARTIALLY;
        }

        return \M2E\Otto\Model\Order::STATUS_UNKNOWN;
    }

    public function convertOttoOrderStatus(string $ottoOrderStatus): int
    {
        $ottoOrderStatus = mb_strtolower($ottoOrderStatus);

        if ($ottoOrderStatus === self::ORDER_STATUS_ANNOUNCED) {
            return \M2E\Otto\Model\Order::STATUS_PENDING;
        }

        if ($ottoOrderStatus === self::ORDER_STATUS_PROCESSABLE) {
            return \M2E\Otto\Model\Order::STATUS_UNSHIPPED;
        }

        if ($ottoOrderStatus === self::ORDER_STATUS_SENT) {
            return \M2E\Otto\Model\Order::STATUS_SHIPPED;
        }

        if ($ottoOrderStatus === self::ORDER_STATUS_RETURNED) {
            return \M2E\Otto\Model\Order::STATUS_RETURNED;
        }

        if (
            $ottoOrderStatus === self::ORDER_STATUS_CANCELLED_BY_PARTNER
            || $ottoOrderStatus === self::ORDER_STATUS_CANCELLED_BY_MARKETPLACE
        ) {
            return \M2E\Otto\Model\Order::STATUS_CANCELED;
        }

        return \M2E\Otto\Model\Order::STATUS_UNKNOWN;
    }
}
