<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Order\ShippingAddress;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractOrder;

class Edit extends AbstractOrder
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
        $this->orderResource->load($order, $orderId);

        $form = $this
            ->getLayout()
            ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Order\Edit\ShippingAddress\Form::class, '', [
                'order' => $order,
            ]);

        $this->setAjaxContent($form->toHtml());

        return $this->getResult();
    }
}
