<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Account\Update;

class Response
{
    private string $installationId;

    public function __construct(string $installationId)
    {
        $this->installationId = $installationId;
    }

    public function getInstallationId(): string
    {
        return $this->installationId;
    }
}
