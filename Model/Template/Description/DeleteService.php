<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Description;

class DeleteService extends \M2E\Otto\Model\Template\AbstractDeleteService
{
    private \M2E\Otto\Model\Template\Description\Repository $descriptionRepository;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Template\Description\Repository $descriptionRepository,
        \M2E\Otto\Model\Listing\Repository $listingRepository
    ) {
        $this->descriptionRepository = $descriptionRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\Otto\Model\Template\PolicyInterface
    {
        return $this->descriptionRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\Otto\Model\Template\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingByDescriptionPolicy($policy->getId());
    }

    protected function delete(\M2E\Otto\Model\Template\PolicyInterface $policy): void
    {
        /** @var \M2E\Otto\Model\Template\Description $policy */
        $this->descriptionRepository->delete($policy);
    }
}
