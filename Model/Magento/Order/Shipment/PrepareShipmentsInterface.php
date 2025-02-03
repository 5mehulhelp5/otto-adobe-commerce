<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Order\Shipment;

interface PrepareShipmentsInterface
{
    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     * @param \Magento\Sales\Model\Order\Item[] $itemsToShip
     *
     * @return \Magento\Sales\Model\Order\Shipment[]
     */
    public function prepareShipments(\Magento\Sales\Model\Order $magentoOrder, array $itemsToShip): array;
}
