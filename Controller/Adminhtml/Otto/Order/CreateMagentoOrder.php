<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Order;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractOrder;

class CreateMagentoOrder extends AbstractOrder
{
    private \M2E\Otto\Model\Order\MagentoProcessor $magentoCreate;
    private \M2E\Otto\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Order\MagentoProcessor $magentoCreate
    ) {
        parent::__construct();
        $this->magentoCreate = $magentoCreate;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $orderIds = $this->getRequestIds();
        $isForce = (bool)$this->getRequest()->getParam('force');
        $warnings = 0;
        $errors = 0;

        foreach ($orderIds as $orderId) {
            $order = $this->orderRepository->find((int)$orderId);
            if ($order === null) {
                continue;
            }

            if (
                $order->getMagentoOrderId() !== null
                && !$isForce
            ) {
                $warnings++;
                continue;
            }

            // Create magento order
            // ---------------------------------------
            try {
                $this->magentoCreate->process($order, $isForce, \M2E\Core\Helper\Data::INITIATOR_USER, false, false);
            } catch (\Throwable $e) {
                $errors++;
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
                    '%count Magento order(s) are already created for the selected %channel_title order(s).',
                    [
                        'count' => $warnings,
                        'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                    ]
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
