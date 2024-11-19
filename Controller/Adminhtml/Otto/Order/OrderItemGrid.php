<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Order;

class OrderItemGrid extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractOrder
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
        $orderId = $this->getRequest()->getParam('id');

        $order = $this->orderFactory->create();
        $this->orderResource->load($order, (int)$orderId);

        if ($order->isObjectNew()) {
            $this->setJsonContent([
                'error' => __('Please specify Required Options.'),
            ]);

            return $this->getResult();
        }

        $orderItemsBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Order\View\Item::class, '', [
                'order' => $order,
            ]);

        $this->setAjaxContent($orderItemsBlock->toHtml());

        return $this->getResult();
    }
}
