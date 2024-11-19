<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Account\AccessParams;

class Install
{
    private ?int $accountId;
    private ?string $title;

    public function __construct(
        ?int $accountId,
        ?string $title
    ) {
        $this->accountId = $accountId;
        $this->title = $title;
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
