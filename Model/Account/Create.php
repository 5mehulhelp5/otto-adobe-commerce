<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Account;

class Create
{
    private \M2E\Otto\Model\Otto\Connector\Account\Add\Processor $addProcessor;
    private Repository $accountRepository;
    private \M2E\Otto\Model\AccountFactory $accountFactory;
    private \M2E\Core\Helper\Magento\Store $storeHelper;

    public function __construct(
        \M2E\Otto\Model\AccountFactory $accountFactory,
        \M2E\Otto\Model\Otto\Connector\Account\Add\Processor $addProcessor,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Core\Helper\Magento\Store $storeHelper
    ) {
        $this->addProcessor = $addProcessor;
        $this->accountRepository = $accountRepository;
        $this->accountFactory = $accountFactory;
        $this->storeHelper = $storeHelper;
    }

    public function create(string $title, string $authCode, string $mode): \M2E\Otto\Model\Account
    {
        $response = $this->createOnServer($title, $authCode, $mode);

        $existAccount = $this->findExistAccountByInstallationId($response->getAccountInstallationId());
        if ($existAccount !== null) {
            throw new \M2E\Otto\Model\Exception(
                'An account with the same details has already been added.
                 Please make sure you provide unique information.',
            );
        } else {
            $account = $this->accountFactory->create();

            $account->init(
                $title,
                $response->getAccountInstallationId(),
                $response->getHash(),
                $mode,
                new \M2E\Otto\Model\Account\Settings\UnmanagedListings(),
                (new \M2E\Otto\Model\Account\Settings\Order())
                    ->createWith(
                        ['listing_other' => ['store_id' => $this->storeHelper->getDefaultStoreId()]],
                    ),
                new \M2E\Otto\Model\Account\Settings\InvoicesAndShipment(),
            );

            $this->accountRepository->create($account);
        }

        return $account;
    }

    private function createOnServer(
        string $title,
        string $authCode,
        string $mode
    ): \M2E\Otto\Model\Otto\Connector\Account\Add\Response {
        return $this->addProcessor->process($title, $authCode, $mode);
    }

    private function findExistAccountByInstallationId(string $installationId): ?\M2E\Otto\Model\Account
    {
        return $this->accountRepository->findByInstallationId($installationId);
    }
}
