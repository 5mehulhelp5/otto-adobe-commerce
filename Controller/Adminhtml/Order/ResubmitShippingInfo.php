<?php

namespace M2E\Otto\Controller\Adminhtml\Order;

class ResubmitShippingInfo extends \M2E\Otto\Controller\Adminhtml\AbstractOrder
{
    private \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory;
    private \M2E\Otto\Model\Order\Repository $orderRepository;
    private \M2E\Otto\Model\Order\ShipmentService $orderShipmentService;

    public function __construct(
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Order\ShipmentService $orderShipmentService,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->orderShipmentService = $orderShipmentService;
    }

    public function execute()
    {
        $orderIds = $this->getRequestIds();

        foreach ($orderIds as $orderId) {
            $order = $this->orderRepository->get((int)$orderId);
            if (!$order->hasMagentoOrder()) {
                continue;
            }

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
            __('Shipping Information has been resend.')
        );

        return $this->_redirect($this->redirect->getRefererUrl());
    }
}
