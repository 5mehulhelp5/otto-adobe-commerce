<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Cron\Task\Order\UploadByUser;

class ManagerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Otto\Model\Account $account): Manager
    {
        return $this->objectManager->create(Manager::class, ['account' => $account]);
    }
}
