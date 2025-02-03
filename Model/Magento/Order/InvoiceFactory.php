<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Order;

class InvoiceFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     * @param \Magento\Sales\Model\Order\Item[] $itemsToInvoice
     *
     * @return \M2E\Otto\Model\Magento\Order\Invoice
     */
    public function create(\Magento\Sales\Model\Order $magentoOrder, array $itemsToInvoice): Invoice
    {
        return $this->objectManager->create(
            Invoice::class,
            ['magentoOrder' => $magentoOrder, 'itemsToInvoice' => $itemsToInvoice]
        );
    }
}
