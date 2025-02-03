<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class LogicItem
{
    /** @var \M2E\Otto\Model\Order\Item[] */
    private array $items;

    /**
     * @param Item[] $items
     */
    public function __construct(array $items)
    {
        if (empty($items)) {
            throw new \M2E\Otto\Model\Exception\Logic('Items are empty');
        }

        $this->items = $items;
    }

    public function getSku(): string
    {
        return $this->getFirst()->getSku();
    }

    public function getTitle(): string
    {
        return $this->getFirst()->getTitle();
    }

    public function getQty(): int
    {
        $sum = 0;
        foreach ($this->items as $item) {
            $sum += $item->getQtyPurchased();
        }

        return $sum;
    }

    public function getSubtotalPrice(): float
    {
        $result = 0;
        foreach ($this->items as $item) {
            if (
                $item->isStatusCancelled()
                || $item->isStatusReturned()
            ) {
                continue;
            }

            $result += $item->getSalePrice() * $item->getQtyPurchased();
        }

        return $result;
    }

    public function isMappedForMagentoProduct(): bool
    {
        try {
            $magentoProduct = $this->getFirst()->getMagentoProduct();
            if ($magentoProduct === null) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }

    public function getMagentoProduct(): \M2E\Otto\Model\Magento\Product
    {
        if (!$this->isMappedForMagentoProduct()) {
            throw new \M2E\Otto\Model\Exception\Logic('Product is not mapped for Magento product.');
        }

        /** @var \M2E\Otto\Model\Magento\Product */
        return $this->getFirst()->getMagentoProduct();
    }

    public function isSomeItemCancelled(): bool
    {
        foreach ($this->items as $item) {
            if ($item->isStatusCancelled()) {
                return true;
            }
        }

        return false;
    }

    public function isSomeItemReturned(): bool
    {
        foreach ($this->items as $item) {
            if ($item->isStatusReturned()) {
                return true;
            }
        }

        return false;
    }

    public function isSomeItemAllowedForCreateInMagento(): bool
    {
        return !empty($this->getItemsAllowedForCreateInMagento());
    }

    /**
     * @return Item[]
     */
    public function getItemsAllowedForCreateInMagento(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if (
                $item->isStatusCancelled()
                || $item->isStatusReturned()
            ) {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    public function isSomeItemAllowedForCreateInvoice(): bool
    {
        return !empty($this->getItemsAllowedForCreateInvoice());
    }

    /**
     * @return Item[]
     */
    public function getItemsAllowedForCreateInvoice(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if (
                !$item->isStatusUnshipped()
                && !$item->isStatusShipped()
            ) {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    public function isSomeItemAllowedForShipment(): bool
    {
        return !empty($this->getItemsAllowedForShipment());
    }

    /**
     * @return Item[]
     */
    public function getItemsAllowedForShipment(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if (!$item->isStatusShipped()) {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    // ----------------------------------------

    private function getFirst(): Item
    {
        return reset($this->items);
    }
}
