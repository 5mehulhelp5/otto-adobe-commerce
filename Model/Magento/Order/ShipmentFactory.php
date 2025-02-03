<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Order;

use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventoryShippingAdminUi\Model\IsOrderSourceManageable;
use Magento\InventoryShippingAdminUi\Model\IsWebsiteInMultiSourceMode;

class ShipmentFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\Otto\Helper\Magento $magentoHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Otto\Helper\Magento $magentoHelper
    ) {
        $this->objectManager = $objectManager;
        $this->magentoHelper = $magentoHelper;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $magentoOrder
     * @param \Magento\Sales\Model\Order\Item[] $itemsToShipment
     *
     * @return \M2E\Otto\Model\Magento\Order\Shipment
     */
    public function create(
        \Magento\Sales\Api\Data\OrderInterface $magentoOrder,
        array $itemsToShipment
    ): \M2E\Otto\Model\Magento\Order\Shipment {
        $prepareShipmentsProcessor = $this->isMsiMode($magentoOrder)
            ? $this->objectManager->get(\M2E\Otto\Model\MSI\Magento\Order\PrepareShipments::class)
            : $this->objectManager->get(\M2E\Otto\Model\Magento\Order\PrepareShipments::class);

        return $this->objectManager->create(
            Shipment::class,
            [
                'magentoOrder' => $magentoOrder,
                'itemsToShip' => $itemsToShipment,
                'prepareShipmentsInterfaceProcessor' => $prepareShipmentsProcessor,
            ]
        );
    }

    private function isMsiMode(\Magento\Sales\Api\Data\OrderInterface $order): bool
    {
        if (!$this->magentoHelper->isMSISupportingVersion()) {
            return false;
        }

        $websiteId = (int)$order->getStore()->getWebsiteId();

        return $this->objectManager->get(IsWebsiteInMultiSourceMode::class)->execute($websiteId)
            && $this->isOrderSourceManageable($order);
    }

    private function isOrderSourceManageable(\Magento\Sales\Api\Data\OrderInterface $order): bool
    {
        if (class_exists(IsOrderSourceManageable::class)) {
            return $this->objectManager->get(IsOrderSourceManageable::class)->execute($order);
        }

        $stocks = $this->objectManager->get(StockRepositoryInterface::class)->getList()->getItems();
        $orderItems = $order->getItems();
        foreach ($orderItems as $orderItem) {
            $isSourceItemManagementAllowed = $this->objectManager->get(
                IsSourceItemManagementAllowedForProductTypeInterface::class
            );

            if (!$isSourceItemManagementAllowed->execute($orderItem->getProductType())) {
                continue;
            }

            /** @var \Magento\InventoryApi\Api\Data\StockInterface $stock */
            foreach ($stocks as $stock) {
                $inventoryConfiguration = $this->objectManager->get(GetStockItemConfigurationInterface::class)->execute(
                    $this->objectManager->get(GetSkuFromOrderItemInterface::class)->execute($orderItem),
                    $stock->getStockId()
                );

                if ($inventoryConfiguration->isManageStock()) {
                    return true;
                }
            }
        }

        return false;
    }
}
