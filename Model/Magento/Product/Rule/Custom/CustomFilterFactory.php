<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Rule\Custom;

class CustomFilterFactory
{
    private array $customFiltersMap = [
        Magento\Qty::NICK => Magento\Qty::class,
        Magento\Stock::NICK => Magento\Stock::class,
        Magento\TypeId::NICK => Magento\TypeId::class,
        Otto\Moin::NICK => Otto\Moin::class,
        Otto\OnlineCategory::NICK => Otto\OnlineCategory::class,
        Otto\OnlineTitle::NICK => Otto\OnlineTitle::class,
        Otto\OnlineQty::NICK => Otto\OnlineQty::class,
        Otto\OnlineSku::NICK => Otto\OnlineSku::class,
        Otto\OnlinePrice::NICK => Otto\OnlinePrice::class,
        Otto\Status::NICK => Otto\Status::class,
    ];

    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createByType(string $type): \M2E\Otto\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
    {
        $filterClass = $this->choiceCustomFilterClass($type);
        if ($filterClass === null) {
            throw new \M2E\Otto\Model\Exception\Logic((string)__('Unknown custom filter - %1', $type));
        }

        return $this->objectManager->create($filterClass);
    }

    private function choiceCustomFilterClass(string $type): ?string
    {
        return $this->customFiltersMap[$type] ?? null;
    }
}
