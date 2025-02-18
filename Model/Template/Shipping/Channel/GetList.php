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
        } catch (\M2E\Otto\Model\Exception\Connection\SystemError $exception) {
            if (
                $exception->getMessageCollection() !== null
                && $this->hasErrorAccountMissingPermissions($exception->getMessageCollection())
            ) {
                throw new \M2E\Otto\Model\Exception\AccountMissingPermissions($account);
            }

            throw $exception;
        }

        return $channelProfiles;
    }

    private function hasErrorAccountMissingPermissions(
        \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection
    ): bool {
        foreach ($messageCollection->getErrors() as $error) {
            if ((int)$error->getCode() === 1403) {
                return true;
            }
        }

        return false;
    }
}
