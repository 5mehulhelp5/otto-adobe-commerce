<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Account\Update;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    /**
     * @param \M2E\Otto\Model\Account $account
     * @param string $authCode
     *
     * @return \M2E\Otto\Model\Otto\Connector\Account\Update\Response
     *
     * @throws \M2E\Otto\Model\Exception
     * @throws \M2E\Otto\Model\Exception\Connection
     * @throws \M2E\Otto\Model\Account\Exception\InstallNotFound
     */
    public function process(
        \M2E\Otto\Model\Account $account,
        string $authCode
    ): Response {
        $command = new \M2E\Otto\Model\Otto\Connector\Account\UpdateCommand(
            $account->getTitle(),
            $account->getServerHash(),
            $authCode,
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
