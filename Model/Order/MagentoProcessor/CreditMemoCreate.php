<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\MagentoProcessor;

class CreditMemoCreate
{
    private \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory;
    private \Magento\Sales\Model\Service\CreditmemoService $creditmemoService;
    private \M2E\Otto\Helper\Module\Exception $helperModuleException;

    public function __construct(
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \M2E\Otto\Helper\Module\Exception $helperModuleException
    ) {
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->helperModuleException = $helperModuleException;
    }

    public function process(
        \M2E\Otto\Model\Order $order
    ): void {
        if (!$this->canCreateCreditMemo($order)) {
            return;
        }

        $creditMemoData = $this->prepareCreditMemoData($order);
        if (empty($creditMemoData)) {
            return;
        }

        try {
            foreach ($creditMemoData as $datum) {
                $invoice = $datum['invoice'];
                $creditMemo = $this->creditmemoFactory->createByInvoice($invoice, ['qtys' => $datum['qtys']]);

                foreach ($creditMemo->getAllItems() as $creditMemoItem) {
                    $creditMemoItem->setBackToStock(true);
                }

                $this->creditmemoService->refund($creditMemo);
            }
        } catch (\Throwable $exception) {
            $this->helperModuleException->process($exception);
            $order->addErrorLog(
                'CreditMemo was not created. Reason: %msg%',
                ['msg' => $exception->getMessage()]
            );

            return;
        }

        $order->addSuccessLog('Credit Memo #%creditMemo_id% was created.', [
            '!creditMemo_id' => $creditMemo->getIncrementId(),
        ]);
    }

    /**
     * @param \M2E\Otto\Model\Order $order
     *
     * @return bool
     */
    private function canCreateCreditMemo(\M2E\Otto\Model\Order $order): bool
    {
        if (!$order->hasMagentoOrder()) {
            return false;
        }

        $magentoOrder = $order->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (!$magentoOrder->canCreditmemo()) {
            return false;
        }

        if (!$magentoOrder->hasInvoices()) {
            return false;
        }

        if (!$order->getAccount()->getOrdersSettings()->isCreateCreditMemoIfOrderCancelledEnabled()) {
            return false;
        }

        if (!$order->isStatusCanceled()) {
            return false;
        }

        return true;
    }

    /**
     * @param \M2E\Otto\Model\Order $order
     *
     * @return array
     */
    private function prepareCreditMemoData(\M2E\Otto\Model\Order $order): array
    {
        /** @var \Magento\Sales\Model\Order\Invoice[] $invoices */
        $invoices = $order->getMagentoOrder()->getInvoiceCollection()->getItems();

        $result = [];
        foreach ($invoices as $invoice) {
            foreach ($invoice->getItems() as $invoiceItem) {
                foreach ($order->getItems() as $orderItem) {
                    if ($invoiceItem->getProductId() != $orderItem->getMagentoProductId()) {
                        continue;
                    }

                    if (!$orderItem->isStatusCancelled()) {
                        continue;
                    }

                    /** @var \Magento\Sales\Model\Order\Item $magentoOrderItem */
                    $magentoOrderItem = $invoiceItem->getOrderItem();
                    if (empty($magentoOrderItem->getQtyToRefund())) {
                        continue;
                    }

                    $result[$invoice->getId()]['invoice'] = $invoice;
                    $result[$invoice->getId()]['qtys'][$magentoOrderItem->getId()] = $magentoOrderItem->getQtyToRefund();
                }
            }
        }

        return $result;
    }
}
