<?php

namespace M2E\Otto\Model\Magento\Order;

class Shipment
{
    /** @var \Magento\Sales\Model\Order */
    protected $magentoOrder;

    /** @var \Magento\Sales\Model\Order\Shipment[] */
    protected $shipments = [];

    // ---------------------------------------

    /** @var \Magento\Framework\DB\TransactionFactory */
    protected $transactionFactory;

    /** @var \M2E\Otto\Model\Magento\Order\Shipment\DocumentFactory */
    protected $shipmentDocumentFactory;

    private \M2E\Otto\Observer\Shipment\EventRuntimeManager $shipmentEventRuntimeManager;

    public function __construct(
        \M2E\Otto\Observer\Shipment\EventRuntimeManager $shipmentEventRuntimeManager,
        \M2E\Otto\Model\Magento\Order\Shipment\DocumentFactory $shipmentDocumentFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory
    ) {
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->transactionFactory = $transactionFactory;
        $this->shipmentEventRuntimeManager = $shipmentEventRuntimeManager;
    }

    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     *
     * @return $this
     */
    public function setMagentoOrder(\Magento\Sales\Model\Order $magentoOrder)
    {
        $this->magentoOrder = $magentoOrder;

        return $this;
    }

    public function getShipments()
    {
        return $this->shipments;
    }

    public function buildShipments()
    {
        $this->prepareShipments();

        $this->shipmentEventRuntimeManager->skipEvents();

        /** @var \Magento\Framework\DB\Transaction $transaction */
        $transaction = $this->transactionFactory->create();
        foreach ($this->shipments as $shipment) {
            // it is necessary for updating qty_shipped field in sales_flat_order_item table
            $shipment->getOrder()->setIsInProcess(true);

            $transaction->addObject($shipment);
            $transaction->addObject($shipment->getOrder());

            $this->magentoOrder->getShipmentsCollection()->addItem($shipment);
        }

        try {
            $transaction->save();
        } catch (\Exception $e) {
            $this->magentoOrder->getShipmentsCollection()->clear();
            throw $e;
        }

        $this->shipmentEventRuntimeManager->doNotSkipEvents();
    }

    protected function prepareShipments()
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->shipmentDocumentFactory->create($this->magentoOrder);
        $shipment->register();

        $this->shipments[] = $shipment;
    }
}
