<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Order;

class PrepareShipments implements \M2E\Otto\Model\Magento\Order\Shipment\PrepareShipmentsInterface
{
    /**
     * @psalm-suppress UndefinedClass
     * @var \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory
     */
    private \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory $itemCreationFactory;
    /** @var \M2E\Otto\Model\Magento\Order\Shipment\DocumentFactory */
    private Shipment\DocumentFactory $shipmentDocumentFactory;

    /**
     * @psalm-suppress UndefinedClass
     */
    public function __construct(
        \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory $itemCreationFactory,
        \M2E\Otto\Model\Magento\Order\Shipment\DocumentFactory $shipmentDocumentFactory
    ) {
        $this->itemCreationFactory = $itemCreationFactory;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     * @param array $itemsToShip
     *
     * @return array|\Magento\Sales\Model\Order\Shipment[]
     */
    public function prepareShipments(\Magento\Sales\Model\Order $magentoOrder, array $itemsToShip): array
    {
        $items = [];
        foreach ($itemsToShip as $magentoOrderItem) {
            $qtyToShip = $magentoOrderItem->getQtyToShip();

            if (empty($qtyToShip)) {
                continue;
            }

            /**
             * @psalm-suppress UndefinedClass
             * @var \Magento\Sales\Api\Data\ShipmentItemCreationInterface $shipmentItem
             */
            $shipmentItem = $this->itemCreationFactory->create();
            $shipmentItem->setQty($qtyToShip);
            $shipmentItem->setOrderItemId($magentoOrderItem->getId());

            $items[$magentoOrderItem->getId()] = $shipmentItem;
        }
        // todo check track

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->shipmentDocumentFactory->create($magentoOrder, $items);
        $shipment->register();

        return [$shipment];
    }
}
