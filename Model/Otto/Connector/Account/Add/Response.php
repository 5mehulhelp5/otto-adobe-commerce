<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Account\Add;

class Response
{
    private string $hash;
    private string $accountInstallationId;

    public function __construct(
        string $hash,
        string $accountInstallationId
    ) {
        $this->hash = $hash;
        $this->accountInstallationId = $accountInstallationId;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getAccountInstallationId(): string
    {
        return $this->accountInstallationId;
    }
}
