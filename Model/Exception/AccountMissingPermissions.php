<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Exception;

class AccountMissingPermissions extends \M2E\Otto\Model\Exception
{
    private \M2E\Otto\Model\Account $account;

    public function __construct(\M2E\Otto\Model\Account $account)
    {
        parent::__construct();

        $this->account = $account;
    }

    public function getAccount(): \M2E\Otto\Model\Account
    {
        return $this->account;
    }
}
