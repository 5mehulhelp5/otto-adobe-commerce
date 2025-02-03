<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Order;

class Shipment
{
    private \Magento\Sales\Model\Order $magentoOrder;

    /** @var \Magento\Sales\Model\Order\Item[] */
    private array $itemsToShip;

    // ---------------------------------------

    private \Magento\Framework\DB\TransactionFactory $transactionFactory;
    private \M2E\Otto\Observer\Shipment\EventRuntimeManager $shipmentEventRuntimeManager;
    private \M2E\Otto\Model\Magento\Order\Shipment\PrepareShipmentsInterface $prepareShipmentsInterfaceProcessor;

    public function __construct(
        \Magento\Sales\Model\Order $magentoOrder,
        array $itemsToShip,
        \M2E\Otto\Model\Magento\Order\Shipment\PrepareShipmentsInterface $prepareShipmentsInterfaceProcessor,
        \M2E\Otto\Observer\Shipment\EventRuntimeManager $shipmentEventRuntimeManager,
        \Magento\Framework\DB\TransactionFactory $transactionFactory
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->shipmentEventRuntimeManager = $shipmentEventRuntimeManager;
        $this->magentoOrder = $magentoOrder;
        $this->itemsToShip = $itemsToShip;
        $this->prepareShipmentsInterfaceProcessor = $prepareShipmentsInterfaceProcessor;
    }

    /**
     * @return \Magento\Sales\Model\Order\Shipment[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(): array
    {
        $shipments = $this->prepareShipmentsInterfaceProcessor->prepareShipments(
            $this->magentoOrder,
            $this->itemsToShip
        );

        $this->shipmentEventRuntimeManager->skipEvents();

        /** @var \Magento\Framework\DB\Transaction $transaction */
        $transaction = $this->transactionFactory->create();
        foreach ($shipments as $shipment) {
            // it is necessary for updating qty_shipped field in sales_flat_order_item table
            $shipment->getOrder()->setIsInProcess(true);

            $transaction->addObject($shipment);
            $transaction->addObject($shipment->getOrder());

            $this->magentoOrder->getShipmentsCollection()->addItem($shipment);
        }

        try {
            $transaction->save();
        } catch (\Throwable $e) {
            $this->magentoOrder->getShipmentsCollection()->clear();

            throw $e;
        }

        $this->shipmentEventRuntimeManager->doNotSkipEvents();

        return $shipments;
    }
}
