<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Shipment;

class ItemLoader
{
    /**
     * @param \M2E\Otto\Model\Order $order
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     *
     * @return \M2E\Otto\Model\Order\Item[]
     */
    public function loadItemsByShipment(
        \M2E\Otto\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment $shipment
    ): array {
        $result = [];
        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
        foreach ($shipment->getAllItems() as $shipmentItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            $orderItem = $shipmentItem->getOrderItem();
            if ($orderItem->getParentItemId() !== null) {
                continue;
            }

            $orderItems = $this->loadItem($order, $shipmentItem);
            if (empty($orderItems)) {
                continue;
            }

            array_push($result, ...$orderItems);
        }

        return $result;
    }

    /**
     * @return \M2E\Otto\Model\Order\Item[]
     */
    private function loadItem(
        \M2E\Otto\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment\Item $shipmentItem
    ): array {
        $magentoProductId = (int)$shipmentItem->getProductId();
        $qty = $shipmentItem->getQty();

        $result = [];
        foreach ($order->getItems() as $item) {
            if (empty($qty)) {
                break;
            }

            if ($magentoProductId !== $item->getMagentoProductId()) {
                continue;
            }

            if (!$item->canUpdateShippingStatus()) {
                continue;
            }

            $result[] = $item;
            $qty--;
        }

        return $result;
    }
}
