<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Account\GetInstallUrl;

class Response
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
