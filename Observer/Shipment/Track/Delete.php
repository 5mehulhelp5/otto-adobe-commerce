<?php

namespace M2E\Otto\Observer\Shipment\Track;

class Delete extends \M2E\Otto\Observer\Shipment\AbstractShipment
{
    private \M2E\Otto\Model\Order\Repository $repository;
    private \M2E\Otto\Helper\Module\Logger $moduleLogger;
    private \M2E\Otto\Model\Order\ShipmentService $orderShipmentService;

    public function __construct(
        \M2E\Otto\Model\Order\ShipmentService $orderShipmentService,
        \M2E\Otto\Model\Order\Repository $repository,
        \M2E\Otto\Helper\Module\Logger $moduleLogger,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
    ) {
        parent::__construct($shipmentCollectionFactory);
        $this->repository = $repository;
        $this->moduleLogger = $moduleLogger;
        $this->orderShipmentService = $orderShipmentService;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $this->getEvent()->getTrack();

        $shipment = $this->getShipment($track);

        if (!$shipment) {
            $class = get_class($this);
            $this->moduleLogger->process(
                [],
                "Otto observer $class cannot get shipment data from event or database"
            );

            return;
        }

        $magentoOrderId = (int)$shipment->getOrderId();

        try {
            $order = $this->repository->findByMagentoOrderId($magentoOrderId);
        } catch (\Throwable $throwable) {
            return;
        }

        if ($order === null) {
            return;
        }

        $this->orderShipmentService->shipByShipment($order, $shipment, \M2E\Core\Helper\Data::INITIATOR_EXTENSION);
    }
}
