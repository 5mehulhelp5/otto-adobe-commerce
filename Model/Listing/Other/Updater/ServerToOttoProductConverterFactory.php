<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other\Updater;

class ServerToOttoProductConverterFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Otto\Model\Account $account): ServerToOttoProductConverter
    {
        return $this->objectManager->create(
            ServerToOttoProductConverter::class,
            [
                'account' => $account,
            ],
        );
    }
}
