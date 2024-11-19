<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\InventorySync;

class AccountLockManager
{
    private const PREFIX_LOCK_NICK = 'synchronization_listing_inventory_for_account_';
    private const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 3600; // 60 min

    private \M2E\Otto\Model\Lock\Item\ManagerFactory $lockItemManagerFactory;

    public function __construct(
        \M2E\Otto\Model\Lock\Item\ManagerFactory $lockItemManagerFactory
    ) {
        $this->lockItemManagerFactory = $lockItemManagerFactory;
    }

    public function isExistByAccount(\M2E\Otto\Model\Account $account): bool
    {
        $lockManager = $this->getLockManager($account);

        if ($lockManager->isExist() === false) {
            return false;
        }

        if ($lockManager->isInactiveMoreThanSeconds(self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $lockManager->remove();

            return false;
        }

        return true;
    }

    public function create(\M2E\Otto\Model\Account $account): void
    {
        $lockManager = $this->getLockManager($account);
        $lockManager->create();
    }

    public function remove(\M2E\Otto\Model\Account $account): void
    {
        $lockManager = $this->getLockManager($account);
        $lockManager->remove();
    }

    private function getLockManager(\M2E\Otto\Model\Account $account): \M2E\Otto\Model\Lock\Item\Manager
    {
        return $this->lockItemManagerFactory->create($this->makeLockNick($account->getId()));
    }

    private function makeLockNick(int $accountId): string
    {
        return self::PREFIX_LOCK_NICK . $accountId;
    }
}
