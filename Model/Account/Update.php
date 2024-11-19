<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Account;

use M2E\Otto\Model\Account\Issue\ValidTokens;

class Update
{
    private \M2E\Otto\Model\Otto\Connector\Account\Update\Processor $updateProcessor;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\Otto\Model\Otto\Connector\Account\Update\Processor $updateProcessor,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Helper\Data\Cache\Permanent $cache
    ) {
        $this->updateProcessor = $updateProcessor;
        $this->accountRepository = $accountRepository;
        $this->cache = $cache;
    }

    public function updateSettings(
        \M2E\Otto\Model\Account $account,
        string $title,
        \M2E\Otto\Model\Account\Settings\UnmanagedListings $unmanagedListingsSettings,
        \M2E\Otto\Model\Account\Settings\Order $orderSettings,
        \M2E\Otto\Model\Account\Settings\InvoicesAndShipment $invoicesAndShipmentSettings
    ): void {
        $account->setTitle($title)
            ->setUnmanagedListingSettings($unmanagedListingsSettings)
            ->setOrdersSettings($orderSettings)
            ->setInvoiceAndShipmentSettings($invoicesAndShipmentSettings);

        $this->accountRepository->save($account);
    }

    /**
     * @param \M2E\Otto\Model\Account $account
     * @param string $authCode
     *
     * @return void
     *
     * @throws \M2E\Otto\Model\Account\Exception\InstallNotFound
     */
    public function updateCredentials(\M2E\Otto\Model\Account $account, string $authCode): void
    {
        $response = $this->updateProcessor->process($account, $authCode);

        $installationId = $response->getInstallationId();
        $account->setInstallationId($installationId);

        $this->accountRepository->save($account);

        $this->cache->removeValue(ValidTokens::ACCOUNT_TOKENS_CACHE_KEY);
    }
}
