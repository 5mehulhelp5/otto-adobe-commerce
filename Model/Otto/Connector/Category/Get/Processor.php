<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Category\Get;

use M2E\Otto\Model\Otto\Connector\Category\Get\Response;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(): Response
    {
        $command = new \M2E\Otto\Model\Otto\Connector\Category\GetCommand();

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
