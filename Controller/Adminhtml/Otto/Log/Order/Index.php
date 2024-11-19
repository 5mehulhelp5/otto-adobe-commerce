<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Log\Order;

class Index extends \M2E\Otto\Controller\Adminhtml\Otto\Log\AbstractOrder
{
    private \M2E\Otto\Model\OrderFactory $orderFactory;
    private \M2E\Otto\Model\ResourceModel\Order $orderResource;

    public function __construct(
        \M2E\Otto\Model\OrderFactory $orderFactory,
        \M2E\Otto\Model\ResourceModel\Order $orderResource
    ) {
        parent::__construct();
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id', false);

        if ($orderId) {
            $order = $this->orderFactory->create();
            $this->orderResource->load($order, (int)$orderId);

            if ($order->isObjectNew()) {
                $this->getMessageManager()->addError(__('Order does not exist.'));

                return $this->_redirect('*/*/index');
            }

            $this->setPageTitle(
                __('Order #%order_id Log', ['order_id' => $order->getOttoOrderNumber()])
            );
        } else {
            $this->setPageTitle(__('Orders Logs & Events'));
        }

        $this->addContent($this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Log\Order::class));

        return $this->getResult();
    }

    private function setPageTitle(string $pageTitle): void
    {
        $this->getResult()->getConfig()->getTitle()->prepend($pageTitle);
    }
}
