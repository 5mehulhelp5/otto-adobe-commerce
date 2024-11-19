<?php

namespace M2E\Otto\Observer\Shipment;

class View extends \M2E\Otto\Observer\AbstractObserver
{
    protected \Magento\Customer\Model\CustomerFactory $customerFactory;
    protected \Magento\Framework\Registry $registry;
    private \M2E\Otto\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Registry $registry,
        \M2E\Otto\Helper\Factory $helperFactory
    ) {
        parent::__construct($helperFactory);
        $this->customerFactory = $customerFactory;
        $this->registry = $registry;
        $this->orderRepository = $orderRepository;
    }

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->registry->registry('current_shipment');
        if (empty($shipment) || !$shipment->getId()) {
            return;
        }

        try {
            $order = $this->orderRepository->findByMagentoOrderId($shipment->getOrderId());
        } catch (\Exception $exception) {
            return;
        }

        if ($order === null) {
            return;
        }

        $customerId = $shipment->getOrder()->getCustomerId();
        if (empty($customerId) || $shipment->getOrder()->getCustomerIsGuest()) {
            return;
        }

        $customer = $this->customerFactory->create()->load($customerId);

        $shipment->getOrder()->setData(
            'customer_' . \M2E\Otto\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(\M2E\Otto\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE)
        );
    }
}
