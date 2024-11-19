<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Synchronization;

class DeleteService extends \M2E\Otto\Model\Template\AbstractDeleteService
{
    private \M2E\Otto\Model\Template\Synchronization\Repository $synchronizationRepository;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Template\Synchronization\Repository $synchronizationRepository,
        \M2E\Otto\Model\Listing\Repository $listingRepository
    ) {
        $this->synchronizationRepository = $synchronizationRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\Otto\Model\Template\PolicyInterface
    {
        return $this->synchronizationRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\Otto\Model\Template\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingBySyncPolicy($policy->getId());
    }

    protected function delete(\M2E\Otto\Model\Template\PolicyInterface $policy): void
    {
        /** @var \M2E\Otto\Model\Template\Synchronization $policy */
        $this->synchronizationRepository->delete($policy);
    }
}
