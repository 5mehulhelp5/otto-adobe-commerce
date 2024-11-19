<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Order;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractOrder;

class CreateMagentoOrder extends AbstractOrder
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
        $orderIds = $this->getRequestIds();
        $isForce = (bool)$this->getRequest()->getParam('force');
        $warnings = 0;
        $errors = 0;

        foreach ($orderIds as $orderId) {
            $order = $this->orderFactory->create();
            $this->orderResource->load($order, (int)$orderId);
            $order->getLogService()->setInitiator(\M2E\Otto\Helper\Data::INITIATOR_USER);

            if ($order->getMagentoOrderId() !== null && !$isForce) {
                $warnings++;
                continue;
            }

            // Create magento order
            // ---------------------------------------
            try {
                $order->createMagentoOrder($isForce);
            } catch (\Exception $e) {
                $errors++;
            }

            // ---------------------------------------

            if ($order->canCreateInvoice()) {
                $order->createInvoice();
            }

            $order->createShipments();

            if ($order->canCreateTracks()) {
                $order->createTracks();
            }
        }

        if (!$errors && !$warnings) {
            $this->messageManager->addSuccess(__('Magento Order(s) were created.'));
        }

        if ($errors) {
            $this->messageManager->addError(
                __(
                    '%count Magento order(s) were not created. Please <a target="_blank" href="%url">view Log</a>
                for the details.',
                    ['count' => $errors, 'url' => $this->getUrl('*/otto_log_order')]
                )
            );
        }

        if ($warnings) {
            $this->messageManager->addWarning(
                __(
                    '%count Magento order(s) are already created for the selected Otto order(s).',
                    ['count' => $warnings]
                )
            );
        }

        if (count($orderIds) == 1) {
            return $this->_redirect('*/*/view', ['id' => $orderIds[0]]);
        } else {
            return $this->_redirect($this->redirect->getRefererUrl());
        }
    }
}
