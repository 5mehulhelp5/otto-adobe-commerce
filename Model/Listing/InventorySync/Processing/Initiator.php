<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\InventorySync\Processing;

class Initiator implements \M2E\Otto\Model\Processing\PartialInitiatorInterface
{
    private \M2E\Otto\Model\Account $account;
    private \M2E\Otto\Model\Listing\InventorySync\AccountLockManager $accountLockManager;

    public function __construct(
        \M2E\Otto\Model\Account $account,
        \M2E\Otto\Model\Listing\InventorySync\AccountLockManager $accountLockManager
    ) {
        $this->account = $account;
        $this->accountLockManager = $accountLockManager;
    }

    public function getInitCommand(): \M2E\Core\Model\Connector\CommandProcessingInterface
    {
        return new Connector\InventoryGetItemsCommand(
            $this->account->getServerHash(),
        );
    }

    public function generateProcessParams(): array
    {
        return [
            'account_id' => $this->account->getId(),
            'current_date' => \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
        ];
    }

    public function getResultHandlerNick(): string
    {
        return ResultHandler::NICK;
    }

    public function initLock(\M2E\Otto\Model\Processing\LockManager $lockManager): void
    {
        $lockManager->create(\M2E\Otto\Model\Account::LOCK_NICK, $this->account->getId());
        $this->accountLockManager->create($this->account);
    }
}
