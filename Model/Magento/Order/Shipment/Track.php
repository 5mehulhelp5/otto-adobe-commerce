<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection as TrackCollection;

class Track
{
    private ?\Magento\Sales\Model\Order $magentoOrder = null;
    private array $supportedCarriers = [];

    private \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory;
    private \M2E\Otto\Observer\Shipment\EventRuntimeManager $shipmentEventRuntimeManager;
    private \M2E\Otto\Model\Order $order;
    private array $trackingDetails = [];

    public function __construct(
        \M2E\Otto\Observer\Shipment\EventRuntimeManager $shipmentEventRuntimeManager,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory,
        \M2E\Otto\Model\Order $order,
        array $trackingDetails
    ) {
        $this->shipmentTrackFactory = $shipmentTrackFactory;
        $this->shipmentEventRuntimeManager = $shipmentEventRuntimeManager;
        $this->order = $order;
        $this->trackingDetails = $trackingDetails;
    }

    public function setSupportedCarriers(array $supportedCarriers): self
    {
        $this->supportedCarriers = $supportedCarriers;

        return $this;
    }

    public function getTracks(): array
    {
        return $this->prepareTracks();
    }

    // ----------------------------------------

    private function prepareTracks(): array
    {
        $trackingDetails = $this->getFilteredTrackingDetails();
        if (count($trackingDetails) == 0) {
            return [];
        }

        // Skip shipment observer
        // ---------------------------------------
        $this->shipmentEventRuntimeManager->skipEvents();
        // ---------------------------------------

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->getMagentoOrder()->getShipmentsCollection()->getFirstItem();

        // Sometimes Magento returns an array instead of Collection by a call of $shipment->getTracksCollection()
        if (
            $shipment->hasData(ShipmentInterface::TRACKS) &&
            !($shipment->getData(ShipmentInterface::TRACKS) instanceof TrackCollection)
        ) {
            $shipment->unsetData(ShipmentInterface::TRACKS);
        }

        $tracks = [];
        foreach ($trackingDetails as $trackingDetail) {
            $track = $this->shipmentTrackFactory->create();
            $track->setNumber($trackingDetail['tracking_number']);
            $track->setTitle((string)$trackingDetail['shipping_carrier']);
            $track->setCarrierCode(
                !empty($trackingDetail['shipping_carrier_service_code'])
                    ? $trackingDetail['shipping_carrier_service_code']
                    : $this->getCarrierCode((string)$trackingDetail['shipping_carrier'])
            );

            $shipment->addTrack($track)->save();
            $tracks[] = $track;
        }

        return $tracks;
    }

    // ---------------------------------------

    private function getFilteredTrackingDetails(): array
    {
        if ($this->getMagentoOrder()->getTracksCollection()->getSize() <= 0) {
            return $this->trackingDetails;
        }

        foreach ($this->getMagentoOrder()->getTracksCollection() as $track) {
            foreach ($this->trackingDetails as $key => $trackingDetail) {
                if (
                    strtolower($track->getData('track_number'))
                    == strtolower($trackingDetail['tracking_number'])
                ) {
                    unset($this->trackingDetails[$key]);
                }
            }
        }

        return $this->trackingDetails;
    }

    private function getMagentoOrder(): ?\Magento\Sales\Model\Order
    {
        if ($this->magentoOrder !== null) {
            return $this->magentoOrder;
        }

        $this->magentoOrder = $this->order->getMagentoOrder();

        return $this->magentoOrder;
    }

    private function getCarrierCode(string $title): string
    {
        $carrierCode = strtolower($title);

        return $this->supportedCarriers[$carrierCode] ?? 'custom';
    }
}
