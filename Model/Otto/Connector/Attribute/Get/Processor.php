<?php

namespace M2E\Otto\Model\Otto\Connector\Attribute\Get;

use M2E\Otto\Model\Otto\Connector\Attribute\Get\Response;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(string $categoryGroupId): Response
    {
        $command = new \M2E\Otto\Model\Otto\Connector\Attribute\GetCommand(
            $categoryGroupId,
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
