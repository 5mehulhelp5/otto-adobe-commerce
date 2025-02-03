<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Order\Shipment;

class DocumentFactory
{
    private \Magento\Sales\Model\Order\ShipmentDocumentFactory $shipmentDocumentFactory;

    public function __construct(
        \Magento\Sales\Model\Order\ShipmentDocumentFactory $shipmentDocumentFactory
    ) {
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Api\Data\ShipmentItemCreationInterface[] $items
     *
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function create(\Magento\Sales\Model\Order $order, array $items): \Magento\Sales\Api\Data\ShipmentInterface
    {
        return $this->shipmentDocumentFactory->create($order, $items);
    }
}
