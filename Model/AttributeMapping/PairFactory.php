<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AttributeMapping;

class PairFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Pair
    {
        return $this->objectManager->create(Pair::class);
    }

    public function create(
        string $type,
        string $channelAttributeTitle,
        string $channelAttributeCode,
        string $magentoAttributeCode
    ): Pair {
        $pair = $this->createEmpty();
        $pair->create($type, $channelAttributeTitle, $channelAttributeCode, $magentoAttributeCode);

        return $pair;
    }
}
