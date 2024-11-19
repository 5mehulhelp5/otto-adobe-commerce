<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other;

class Reset
{
    private Repository $listingOtherRepository;
    private \M2E\Otto\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        Repository $listingOtherRepository
    ) {
        $this->listingOtherRepository = $listingOtherRepository;
        $this->accountRepository = $accountRepository;
    }

    public function process(\M2E\Otto\Model\Account $account): void
    {
        $this->listingOtherRepository->removeByAccountId($account->getId());
        $account->resetInventoryLastSyncData();
        $this->accountRepository->save($account);
    }
}
