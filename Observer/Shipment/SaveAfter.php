<?php

declare(strict_types=1);

namespace M2E\Otto\Observer\Shipment;

class SaveAfter extends \M2E\Otto\Observer\AbstractObserver
{
    /** @var \M2E\Otto\Observer\Shipment\EventRuntimeManager */
    private EventRuntimeManager $eventRuntimeManager;
    private \M2E\Otto\Model\Order\Repository $repository;
    private \M2E\Otto\Model\Order\ShipmentService $orderShipmentService;

    public function __construct(
        \M2E\Otto\Model\Order\ShipmentService $orderShipmentService,
        \M2E\Otto\Observer\Shipment\EventRuntimeManager $eventRuntimeManager,
        \M2E\Otto\Model\Order\Repository $repository
    ) {
        $this->eventRuntimeManager = $eventRuntimeManager;
        $this->repository = $repository;
        $this->orderShipmentService = $orderShipmentService;
    }

    protected function process(): void
    {
        if ($this->eventRuntimeManager->isNeedSkipEvents()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->getEvent()->getObject();

        $magentoOrderId = (int)$shipment->getOrderId();

        try {
            $order = $this->repository->findByMagentoOrderId($magentoOrderId);
        } catch (\Throwable $e) {
            return;
        }

        if ($order === null) {
            return;
        }

        $this->orderShipmentService->shipByShipment($order, $shipment, \M2E\Core\Helper\Data::INITIATOR_EXTENSION);
    }
}
