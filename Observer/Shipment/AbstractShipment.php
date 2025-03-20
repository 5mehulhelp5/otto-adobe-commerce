<?php

namespace M2E\Otto\Observer\Shipment;

abstract class AbstractShipment extends \M2E\Otto\Observer\AbstractObserver
{
    private \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
    ) {
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Item|\Magento\Sales\Model\Order\Shipment\Track $source
     *
     * @return \Magento\Sales\Model\Order\Shipment|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getShipment($source): ?\Magento\Sales\Model\Order\Shipment
    {
        $shipment = $source->getShipment();
        if (
            $shipment !== null
            && $shipment->getId()
        ) {
            return $shipment;
        }

        $shipmentCollection = $this->shipmentCollectionFactory->create();
        $shipmentCollection->addFieldToFilter('entity_id', $source->getParentId());

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $shipmentCollection->getFirstItem();
        if ($shipment->isObjectNew()) {
            return null;
        }

        return $shipment;
    }
}
