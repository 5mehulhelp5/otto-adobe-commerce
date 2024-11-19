<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Account\AccessParams;

class GrantAccess
{
    private string $authCode;
    private ?int $accountId;
    private ?string $title;
    private string $mode;

    public function __construct(
        string $authCode,
        ?int $accountId,
        ?string $title,
        string $mode
    ) {
        $this->authCode = $authCode;
        $this->accountId = $accountId;
        $this->title = $title;
        $this->mode = $mode;
    }

    public function getAuthCode(): string
    {
        return $this->authCode;
    }

    public function hasAccountId(): bool
    {
        return !empty($this->accountId);
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getMode(): string
    {
        return $this->mode;
    }
}
