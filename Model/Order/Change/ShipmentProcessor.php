<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Change;

class ShipmentProcessor
{
    private const MAX_UPDATES_PER_TIME = 50;

    /** @var \M2E\Otto\Model\Order\Change\Repository */
    private Repository $changeRepository;
    private \M2E\Otto\Model\Order\Shipment\Config $shipmentConfig;
    private \M2E\Otto\Model\Order\Repository $orderRepository;
    private \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Processor $connectorProcessor;

    public function __construct(
        \M2E\Otto\Model\Order\Change\Repository $changeRepository,
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Order\Shipment\Config $shipmentConfig,
        \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Processor $connectorProcessor
    ) {
        $this->changeRepository = $changeRepository;
        $this->shipmentConfig = $shipmentConfig;
        $this->orderRepository = $orderRepository;
        $this->connectorProcessor = $connectorProcessor;
    }

    public function process(\M2E\Otto\Model\Account $account): void
    {
        $changes = $this->changeRepository->findShippingForProcess($account, self::MAX_UPDATES_PER_TIME);
        if (empty($changes)) {
            return;
        }

        /**
         * @var array<string, array{order: \M2E\Otto\Model\Order, change: \M2E\Otto\Model\Order\Change}> $processedChanges
         */
        $processedChanges = [];

        $shipFromCity = $this->shipmentConfig->getBaseShippingCity();
        $shipFromCountryCode = $this->shipmentConfig->getBaseShippingCountry();
        $shipFromZipCode = $this->shipmentConfig->getBaseShippingZip();

        $requestPackages = [];
        foreach ($changes as $change) {
            $order = $this->orderRepository->find($change->getOrderId());
            if (
                $order === null
                || !$order->canUpdateShippingStatus()
            ) {
                $this->removeChange($change);
                continue;
            }

            $this->changeIncreaseAttempt($change);

            $changeParams = $change->getParams();

            $shipItems = [];
            foreach ($changeParams['items'] as $itemData) {
                $orderItem = $order->findItem((int)$itemData['item_id']);
                if (
                    $orderItem === null
                    || !$orderItem->canUpdateShippingStatus()
                ) {
                    continue;
                }

                $shipItems[] = new \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\PositionItem(
                    $orderItem->getOttoItemId(),
                    $order->getOttoOrderId(),
                    $changeParams['return_carrier_code'] ?? null,
                    $changeParams['return_tracking_number'] ?? null,
                    $orderItem->getDeliveryType()
                );
            }

            if (empty($shipItems)) {
                continue;
            }

            $processedChanges[$order->getOttoOrderId()] = [
                'order' => $order,
                'change' => $change,
            ];

            $requestPackages[] = new \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Package(
                $order->getOttoOrderId(),
                $changeParams['shipping_carrier_service_code'],
                $changeParams['tracking_number'],
                $changeParams['ship_date'],
                $shipFromCity,
                $shipFromCountryCode,
                $shipFromZipCode,
                $shipItems,
            );
        }

        if (empty($requestPackages)) {
            $this->removeAllChanges($changes);

            return;
        }

        $response = $this->connectorProcessor->process(
            $account,
            $requestPackages,
        );

        foreach ($response->getErrors() as $error) {
            if (!isset($processedChanges[$error->getPackageId()])) {
                continue;
            }

            $order = $processedChanges[$error->getPackageId()]['order'];
            $message = (string)__(
                'Shipping order error. Reason: %reason',
                ['reason' => $error->getMessage()],
            );
            $order->addErrorLog($message);

            $this->removeChange($processedChanges[$error->getPackageId()]['change']);

            unset($processedChanges[$error->getPackageId()]);
        }

        foreach ($processedChanges as $changeData) {
            $changeParams = $changeData['change']->getParams();

            $carrier = $changeParams['shipping_carrier_service_code'];
            $tracking = $changeParams['tracking_number'];
            $returnCarrier = $changeParams['return_carrier_code'] ?? null;
            $returnTracking = $changeParams['return_tracking_number'] ?? null;

            $params = [
                '!tracking' => $tracking,
                '!carrier' => $carrier,
            ];
            $message = 'Order status was updated to Shipped. Tracking number %tracking% for %carrier% has been sent to Otto.';

            if ($returnCarrier && $returnTracking) {
                $message = 'Order status was updated to Shipped. Tracking number %tracking% for %carrier% and return' .
                    ' tracking number %returntracking% for %returncarrier% has been sent to Otto.';
                $params['!returntracking'] = $returnTracking;
                $params['!returncarrier'] = $returnCarrier;
            }

            $changeData['order']->addSuccessLog($message, $params);

            $this->removeChange($changeData['change']);
        }
    }

    private function changeIncreaseAttempt(\M2E\Otto\Model\Order\Change $change): void
    {
        $change->increaseAttempt();

        $this->changeRepository->save($change);
    }

    private function removeChange(\M2E\Otto\Model\Order\Change $change): void
    {
        $this->changeRepository->remove($change);
    }

    private function removeAllChanges(array $changes): void
    {
        foreach ($changes as $change) {
            $this->removeChange($change);
        }
    }
}
