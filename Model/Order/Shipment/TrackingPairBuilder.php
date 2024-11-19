<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Shipment;

use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection as TrackCollection;

class TrackingPairBuilder
{
    private \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory;

    public function __construct(\Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory)
    {
        $this->carrierFactory = $carrierFactory;
    }

    public function build(
        \M2E\Otto\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment $shipment
    ): \M2E\Otto\Model\Order\Shipment\TrackingPair {
        $tracks = $shipment->getTracks();
        if (empty($tracks)) {
            $tracks = $shipment->getTracksCollection();
        }

        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        if ($tracks instanceof TrackCollection) {
            $track = $tracks->getFirstItem();
            $returnTrack = $tracks->getLastItem();
        } else {
            $track = reset($tracks);
            $returnTrack = end($tracks);
        }

        $primaryDetails = $this->createTrackingDetails($track, $order);

        $returnDetails = null;
        if ($track->getTrackNumber() !== $returnTrack->getTrackNumber()) {
            $returnDetails = $this->createTrackingDetails($returnTrack, $order);
        }

        return new \M2E\Otto\Model\Order\Shipment\TrackingPair($primaryDetails, $returnDetails);
    }

    private function createTrackingDetails(
        \Magento\Sales\Model\Order\Shipment\Track $track,
        \M2E\Otto\Model\Order $order
    ): ?\M2E\Otto\Model\Order\Shipment\TrackingDetails {
        $number = trim((string)$track->getNumber());
        if (empty($number)) {
            return null;
        }

        $carrierCode = $carrierTitle = trim((string)$track->getCarrierCode());
        $carrier = $this->carrierFactory->create($carrierCode, $order->getStoreId());
        if ($carrier) {
            $carrierTitle = $carrier->getConfigData('title');
        }

        if ($carrierCode === \Magento\Sales\Model\Order\Shipment\Track::CUSTOM_CARRIER_CODE) {
            $carrierCode = $track->getTitle();
        }

        return new \M2E\Otto\Model\Order\Shipment\TrackingDetails(
            $carrierCode,
            $carrierTitle,
            trim((string)$track->getTitle()),
            $number,
        );
    }
}
