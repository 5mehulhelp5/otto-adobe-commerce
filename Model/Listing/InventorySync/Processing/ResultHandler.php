<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\InventorySync\Processing;

class ResultHandler implements \M2E\Otto\Model\Processing\PartialResultHandlerInterface
{
    public const NICK = 'listing_inventory_sync';

    private \M2E\Otto\Model\Account\Repository $accountRepository;

    private \M2E\Otto\Model\Account $account;
    private \M2E\Otto\Model\Listing\Other\UpdaterFactory $listingOtherUpdaterFactory;
    private \M2E\Otto\Model\Listing\InventorySync\AccountLockManager $accountLockManager;
    private \M2E\Otto\Model\Product\UpdateFromChannel $productUpdateFromChannelProcessor;
    private \M2E\Otto\Model\ExternalChange\Processor $externalChangeProcessor;
    private \M2E\Otto\Model\Listing\Other\Updater\ServerToOttoProductConverterFactory $otherConverterFactory;

    private \DateTime $fromDate;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Listing\Other\UpdaterFactory $listingOtherUpdaterFactory,
        \M2E\Otto\Model\Listing\InventorySync\AccountLockManager $accountLockManager,
        \M2E\Otto\Model\Product\UpdateFromChannel $productUpdateFromChannelProcessor,
        \M2E\Otto\Model\ExternalChange\Processor $externalChangeProcessor,
        \M2E\Otto\Model\Listing\Other\Updater\ServerToOttoProductConverterFactory $otherConverterFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->listingOtherUpdaterFactory = $listingOtherUpdaterFactory;
        $this->accountLockManager = $accountLockManager;
        $this->productUpdateFromChannelProcessor = $productUpdateFromChannelProcessor;
        $this->externalChangeProcessor = $externalChangeProcessor;
        $this->otherConverterFactory = $otherConverterFactory;
    }

    public function initialize(array $params): void
    {
        if (!isset($params['account_id'])) {
            throw new \M2E\Otto\Model\Exception\Logic('Processing params is not valid.');
        }

        $account = $this->accountRepository->find($params['account_id']);
        if ($account === null) {
            throw new \M2E\Otto\Model\Exception('Account not found');
        }

        $this->account = $account;

        if (isset($params['current_date'])) {
            $this->fromDate = \M2E\Core\Helper\Date::createDateGmt($params['current_date']);
        }
    }

    public function processPartialResult(array $partialData): void
    {
        $converter = $this->otherConverterFactory->create($this->account);
        $ottoProductsCollection = $converter->convert($partialData);

        $this->externalChangeProcessor->processReceivedProducts($this->account, $ottoProductsCollection);

        $existInListingCollection = $this->listingOtherUpdaterFactory
            ->create($this->account)
            ->process($ottoProductsCollection);

        /** @psalm-suppress RedundantCondition */
        if ($existInListingCollection !== null) {
            $this->productUpdateFromChannelProcessor
                ->process($existInListingCollection, $this->account);
        }
    }

    public function processSuccess(array $resultData, array $messages): void
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->fromDate)) {
            $this->account->setInventoryLastSyncDate(clone $this->fromDate);

            $this->accountRepository->save($this->account);
            $inventorySyncProcessingStartDate = $this->fromDate;
        } else {
            $inventorySyncProcessingStartDate = \M2E\Core\Helper\Date::createCurrentGmt();
        }

        $this->externalChangeProcessor
            ->processDeletedProducts($this->account, $inventorySyncProcessingStartDate);
    }

    public function processExpire(): void
    {
        // do nothing
    }

    public function clearLock(\M2E\Otto\Model\Processing\LockManager $lockManager): void
    {
        $lockManager->delete(\M2E\Otto\Model\Account::LOCK_NICK, $this->account->getId());
        $this->accountLockManager->remove($this->account);
    }
}
