<?php

namespace M2E\Otto\Model\Lock\Item;

class ManagerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $nick): \M2E\Otto\Model\Lock\Item\Manager
    {
        return $this->objectManager->create(
            \M2E\Otto\Model\Lock\Item\Manager::class,
            [
                'nick' => $nick,
            ]
        );
    }
}
