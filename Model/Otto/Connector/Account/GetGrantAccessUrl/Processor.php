<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Account\GetGrantAccessUrl;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(string $backUrl, string $mode): Response
    {
        $command = new \M2E\Otto\Model\Otto\Connector\Account\GetGrantAccessUrlCommand(
            $backUrl,
            $mode
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
