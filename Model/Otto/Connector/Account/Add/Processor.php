<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Account\Add;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(string $title, string $authCode, string $mode): Response
    {
        $command = new \M2E\Otto\Model\Otto\Connector\Account\AddCommand($title, $authCode, $mode);

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
