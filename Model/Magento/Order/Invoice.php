<?php

namespace M2E\Otto\Model\Magento\Order;

class Invoice
{
    private \Magento\Sales\Model\Order $magentoOrder;
    /** @var \Magento\Sales\Model\Order\Item[] */
    private array $itemsToInvoice;
    private \Magento\Framework\DB\TransactionFactory $transactionFactory;

    public function __construct(
        \Magento\Sales\Model\Order $magentoOrder,
        array $itemsToInvoice,
        \Magento\Framework\DB\TransactionFactory $transactionFactory
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->magentoOrder = $magentoOrder;
        $this->itemsToInvoice = $itemsToInvoice;
    }

    public function create(): \Magento\Sales\Model\Order\Invoice
    {
        $itemsToInvoice = $this->prepareItems();
        if (empty($itemsToInvoice)) {
            throw new \M2E\Otto\Model\Exception\Logic('No items to invoice');
        }

        // Create invoice
        // ---------------------------------------
        $invoice = $this->magentoOrder->prepareInvoice($itemsToInvoice);
        $invoice->register();
        // it is necessary for updating qty_invoiced field in sales_flat_order_item table
        $invoice->getOrder()->setIsInProcess(true);

        $this->transactionFactory
            ->create()
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        // ---------------------------------------

        return $invoice;
    }

    private function prepareItems(): array
    {
        $result = [];
        foreach ($this->itemsToInvoice as $magentoOrderItem) {
            $qtyToInvoice = $magentoOrderItem->getQtyToInvoice();

            if (empty($qtyToInvoice)) {
                continue;
            }

            $result[$magentoOrderItem->getId()] = $qtyToInvoice;
        }

        return $result;
    }
}
