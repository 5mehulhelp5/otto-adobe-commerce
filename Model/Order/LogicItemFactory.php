<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class LogicItemFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param Item[] $items
     *
     * @return \M2E\Otto\Model\Order\LogicItem
     */
    public function create(array $items): LogicItem
    {
        return $this->objectManager->create(LogicItem::class, ['items' => array_values($items)]);
    }
}
