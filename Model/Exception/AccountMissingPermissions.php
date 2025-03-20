<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Exception;

class AccountMissingPermissions extends \M2E\Otto\Model\Exception
{
    private \M2E\Otto\Model\Account $account;

    public function __construct(
        \M2E\Otto\Model\Account $account,
        string $message = '',
        array $additionalData = [],
        int $code = 0
    ) {
        parent::__construct($message, $additionalData, $code);

        $this->account = $account;
    }

    public function getAccount(): \M2E\Otto\Model\Account
    {
        return $this->account;
    }
}
