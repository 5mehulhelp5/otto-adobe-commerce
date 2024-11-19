<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Order;

class UpdateShippingStatus extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractOrder
{
    private \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory;
    private \M2E\Otto\Model\Order\Repository $orderRepository;
    private \M2E\Otto\Model\Order\ShipmentService $orderShipmentService;

    public function __construct(
        \M2E\Otto\Model\Order\ShipmentService $orderShipmentService,
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory
    ) {
        parent::__construct();

        $this->orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->orderShipmentService = $orderShipmentService;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function execute()
    {
        $orderIds = $this->getRequestIds();

        if (empty($orderIds)) {
            $this->messageManager->addError(__('Please select Order(s).'));

            return false;
        }

        foreach ($orderIds as $orderId) {
            $order = $this->orderRepository->get((int)$orderId);
            if (!$order->hasMagentoOrder()) {
                continue;
            }

            /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipmentsCollection */
            $shipmentsCollection = $this->orderShipmentCollectionFactory->create();
            $shipmentsCollection->setOrderFilter($order->getMagentoOrderId());

            foreach ($shipmentsCollection->getItems() as $shipment) {
                /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                if (!$shipment->getId()) {
                    continue;
                }

                $this->orderShipmentService->shipByShipment($order, $shipment, \M2E\Otto\Helper\Data::INITIATOR_USER);
            }
        }

        $this->messageManager->addSuccess(
            __('Updating Order(s) Status to Shipped in Progress...')
        );

        return $this->_redirect($this->redirect->getRefererUrl());
    }
}
