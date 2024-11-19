<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping\Channel;

class Delete
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;
    private \M2E\Otto\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Connector\Client\Single $serverClient
    ) {
        $this->accountRepository = $accountRepository;
        $this->serverClient = $serverClient;
    }

    public function process(\M2E\Otto\Model\Template\Shipping $profile): void
    {
        $account = $this->accountRepository->get($profile->getAccountId());
        $command = new \M2E\Otto\Model\Otto\Connector\ShippingProfile\DeleteCommand($account->getServerHash(), $profile->getShippingProfileId());

        $this->serverClient->process($command);
    }
}
