<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

class LogicItemCollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    /** @var \M2E\Otto\Model\Order\LogicItemFactory */
    private LogicItemFactory $logicItemFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Otto\Model\Order\LogicItemFactory $logicItemFactory
    ) {
        $this->objectManager = $objectManager;
        $this->logicItemFactory = $logicItemFactory;
    }

    /**
     * @param Item[] $items
     *
     * @return \M2E\Otto\Model\Order\LogicItemCollection
     */
    public function create(array $items): LogicItemCollection
    {
        $collection = $this->objectManager->create(LogicItemCollection::class);

        $groupBySku = [];
        foreach ($items as $item) {
            $sku = $item->getSku();
            $groupBySku[$sku][] = $item;
        }

        foreach ($groupBySku as $itemsForSku) {
            $collection->addItem($this->logicItemFactory->create($itemsForSku));
        }

        return $collection;
    }

    public function createFromOrder(\M2E\Otto\Model\Order $order): LogicItemCollection
    {
        return $this->create($order->getItems());
    }
}
