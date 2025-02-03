<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class LogicItemCollection
{
    /** @var \M2E\Otto\Model\Order\LogicItem[] */
    private array $items = [];

    public function addItem(LogicItem $item): void
    {
        $this->items[$item->getSku()] = $item;
    }

    public function findBySku(string $sku): ?LogicItem
    {
        return $this->items[$sku] ?? null;
    }

    // ----------------------------------------

    /**
     * @return \M2E\Otto\Model\Order\LogicItem[]
     */
    public function getAllowedForCreateInMagento(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item->isSomeItemAllowedForCreateInMagento()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return \M2E\Otto\Model\Order\LogicItem[]
     */
    public function getAllowedForCreateInvoice(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item->isSomeItemAllowedForCreateInvoice()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return \M2E\Otto\Model\Order\LogicItem[]
     */
    public function getAllowedForShipment(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item->isSomeItemAllowedForShipment()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return \M2E\Otto\Model\Order\LogicItem[]
     */
    public function getAll(): array
    {
        return array_values($this->items);
    }
}
