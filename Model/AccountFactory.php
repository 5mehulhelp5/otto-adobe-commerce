<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\Account;

class AccountFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(): Account
    {
        return $this->objectManager->create(Account::class);
    }
}
