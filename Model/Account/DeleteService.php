<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Account;

use M2E\Otto\Model\Account\Issue\ValidTokens;

class DeleteService
{
    private Repository $accountRepository;
    private \M2E\Otto\Model\Order\Repository $orderRepository;
    private \M2E\Otto\Model\Order\Log\Repository $orderLogRepository;
    private \M2E\Otto\Model\Listing\Log\Repository $listingLogRepository;
    private \M2E\Otto\Helper\Module\Exception $exceptionHelper;
    private \M2E\Otto\Helper\Data\Cache\Permanent $cache;
    private \M2E\Otto\Model\Listing\Other\Repository $listingOtherRepository;
    private \M2E\Otto\Model\Listing\DeleteService $listingDeleteService;

    public function __construct(
        Repository $accountRepository,
        \M2E\Otto\Model\Listing\DeleteService $listingDeleteService,
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Order\Log\Repository $orderLogRepository,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper,
        \M2E\Otto\Model\Listing\Log\Repository $listingLogRepository,
        \M2E\Otto\Model\Listing\Other\Repository $listingOtherRepository,
        \M2E\Otto\Helper\Data\Cache\Permanent $cache
    ) {
        $this->accountRepository = $accountRepository;
        $this->orderRepository = $orderRepository;
        $this->orderLogRepository = $orderLogRepository;
        $this->listingLogRepository = $listingLogRepository;
        $this->exceptionHelper = $exceptionHelper;
        $this->cache = $cache;
        $this->listingOtherRepository = $listingOtherRepository;
        $this->listingDeleteService = $listingDeleteService;
    }

    /**
     * @param \M2E\Otto\Model\Account $account
     *
     * @return void
     * @throws \Throwable
     */
    public function delete(\M2E\Otto\Model\Account $account): void
    {
        $accountId = $account->getId();

        try {
            $this->orderLogRepository->removeByAccountId($accountId);

            $this->orderRepository->removeByAccountId($accountId);

            $this->listingLogRepository->removeByAccountId($accountId);

            $this->listingOtherRepository->removeByAccountId($accountId);

            $this->removeListings($account);

            $this->deleteAccount($account);
        } catch (\Throwable $e) {
            $this->exceptionHelper->process($e);
            throw $e;
        }
    }

    private function removeListings(\M2E\Otto\Model\Account $account): void
    {
        foreach ($account->getListings() as $listing) {
            $this->listingDeleteService->process($listing, true);
        }
    }

    private function deleteAccount(\M2E\Otto\Model\Account $account): void
    {
        $this->cache->removeTagValues('account');

        $this->accountRepository->remove($account);

        $this->cache->removeValue(ValidTokens::ACCOUNT_TOKENS_CACHE_KEY);
    }
}
