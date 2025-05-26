<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\MagentoProcessor;

class HandleOrder
{
    private \M2E\Otto\Model\Magento\Order\Updater $magentoOrderUpdater;

    public function __construct(
        \M2E\Otto\Model\Magento\Order\Updater $magentoOrderUpdater
    ) {
        $this->magentoOrderUpdater = $magentoOrderUpdater;
    }

    /**
     * @param \M2E\Otto\Model\Order $order
     * @param bool $isForce
     * @param int $initiator
     * @param bool $addLogAboutCreate
     *
     * @return void
     * @throws \M2E\Otto\Model\Order\Exception\UnableCreateMagentoOrder
     */
    public function process(
        \M2E\Otto\Model\Order $order,
        bool $isForce,
        int $initiator,
        bool $addLogAboutCreate
    ): void {
        $this->createMagentoOrderIfNeed($order, $isForce, $initiator, $addLogAboutCreate);
        $this->updateMagentoOrderIfNeed($order);
    }

    private function createMagentoOrderIfNeed(
        \M2E\Otto\Model\Order $order,
        bool $isForce,
        int $initiator,
        bool $addLogAboutCreate
    ): void {
        if (!$order->canCreateMagentoOrder()) {
            return;
        }

        $order->getLogService()->setInitiator($initiator);

        if ($addLogAboutCreate) {
            $this->writeLogAboutCreate($order);
        }

        try {
            $order->createMagentoOrder($isForce);
        } catch (\Throwable $e) {
            throw new \M2E\Otto\Model\Order\Exception\UnableCreateMagentoOrder(
                $e->getMessage(),
                ['order_id' => $order->getId()],
                0,
                $e
            );
        }
    }

    private function updateMagentoOrderIfNeed(\M2E\Otto\Model\Order $order): void
    {
        if (
            !$order->getAccount()->getOrdersSettings()->isOrderStatusMappingModeDefault()
            || $order->getStatusUpdateRequired()
        ) {
            $magentoOrder = $order->getMagentoOrder();
            if ($magentoOrder === null) {
                return;
            }

            $this->magentoOrderUpdater->setMagentoOrder($magentoOrder);
            $this->magentoOrderUpdater->updateStatus($order->getStatusForMagentoOrder());

            $this->magentoOrderUpdater->finishUpdate();
        }
    }

    private function writeLogAboutCreate(\M2E\Otto\Model\Order $order): void
    {
        $order->addInfoLog(
            strtr(
                'Magento order creation rules are met. :extension_title will attempt to create Magento order.',
                [
                    ':extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                ]
            ),
            [],
            [],
            true
        );
    }
}
