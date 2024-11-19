<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping\Channel;

class Update
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\Otto\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
    }

    public function process(\M2E\Otto\Model\Account $account, \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile $channelProfile): void
    {
        $command = new \M2E\Otto\Model\Otto\Connector\ShippingProfile\UpdateCommand($account->getServerHash(), $channelProfile);
        $this->serverClient->process($command);
    }
}
