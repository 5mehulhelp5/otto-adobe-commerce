<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Brand\Get;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(
        \M2E\Otto\Model\Account $account,
        array $names
    ): Response {
        $command = new \M2E\Otto\Model\Otto\Connector\Brand\GetCommand(
            $account->getServerHash(),
            $names,
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
