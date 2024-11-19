<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other;

class UpdaterFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Otto\Model\Account $account): Updater
    {
        return $this->objectManager->create(
            Updater::class,
            [
                'account' => $account
            ],
        );
    }
}
