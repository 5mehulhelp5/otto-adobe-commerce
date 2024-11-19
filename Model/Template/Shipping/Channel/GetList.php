<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping\Channel;

class GetList
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\Otto\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
    }

    public function process(\M2E\Otto\Model\Account $account): ShippingProfileCollection
    {
        $command = new \M2E\Otto\Model\Otto\Connector\ShippingProfile\GetListCommand($account->getServerHash());
        /** @var ShippingProfileCollection $channelProfiles */
        try {
            $channelProfiles = $this->serverClient->process($command);
        } catch (\M2E\Otto\Model\Exception\Connection\SystemError $e) {
            if ($e->getMessageCollection() !== null && $e->getMessageCollection()->hasErrorWithCode(1403)) {
                throw new \M2E\Otto\Model\Exception\AccountMissingPermissions($account);
            }
            throw $e;
        }

        return $channelProfiles;
    }
}
