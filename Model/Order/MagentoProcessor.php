<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class MagentoProcessor
{
    /** @var \M2E\Otto\Model\Order\MagentoProcessor\InvoiceCreate */
    private MagentoProcessor\InvoiceCreate $magentoInvoiceCreate;
    /** @var \M2E\Otto\Model\Order\MagentoProcessor\ShipmentCreate */
    private MagentoProcessor\ShipmentCreate $magentoShipmentCreate;
    /** @var \M2E\Otto\Model\Order\MagentoProcessor\CreditMemoCreate */
    private MagentoProcessor\CreditMemoCreate $magentoCreditMemoCreate;
    /** @var \M2E\Otto\Model\Order\MagentoProcessor\ShipmentTrackCreate */
    private MagentoProcessor\ShipmentTrackCreate $magentoTrackCreate;
    /** @var \M2E\Otto\Model\Order\MagentoProcessor\HandleOrder */
    private MagentoProcessor\HandleOrder $handleOrder;

    public function __construct(
        MagentoProcessor\HandleOrder $orderCreate,
        MagentoProcessor\InvoiceCreate $magentoInvoiceCreate,
        MagentoProcessor\ShipmentCreate $magentoShipmentCreate,
        MagentoProcessor\CreditMemoCreate $magentoCreditMemoCreate,
        MagentoProcessor\ShipmentTrackCreate $magentoTrackCreate
    ) {
        $this->magentoInvoiceCreate = $magentoInvoiceCreate;
        $this->magentoShipmentCreate = $magentoShipmentCreate;
        $this->magentoCreditMemoCreate = $magentoCreditMemoCreate;
        $this->magentoTrackCreate = $magentoTrackCreate;
        $this->handleOrder = $orderCreate;
    }

    /**
     * @param \M2E\Otto\Model\Order $order
     * @param bool $isForce
     * @param int $initiator
     * @param bool $processReserve
     * @param bool $addLogAboutCreate
     *
     * @return void
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \M2E\Otto\Model\Order\Exception\UnableCreateMagentoOrder
     */
    public function process(
        \M2E\Otto\Model\Order $order,
        bool $isForce,
        int $initiator,
        bool $processReserve,
        bool $addLogAboutCreate
    ): void {
        $this->handleOrder->process($order, $isForce, $initiator, $addLogAboutCreate);

        if ($processReserve) {
            if (
                $order->getReserve()->isNotProcessed()
                && $order->isReservable()
            ) {
                $order->getReserve()->place();
            }
        }

        $this->magentoInvoiceCreate->process($order);
        $this->magentoShipmentCreate->process($order);
        $this->magentoTrackCreate->process($order);
        $this->magentoCreditMemoCreate->process($order);
    }
}
