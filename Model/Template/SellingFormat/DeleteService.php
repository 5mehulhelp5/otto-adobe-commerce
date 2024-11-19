<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\SellingFormat;

class DeleteService extends \M2E\Otto\Model\Template\AbstractDeleteService
{
    private \M2E\Otto\Model\Template\SellingFormat\Repository $sellingFormatRepository;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Template\SellingFormat\Repository $sellingFormatRepository,
        \M2E\Otto\Model\Listing\Repository $listingRepository
    ) {
        $this->sellingFormatRepository = $sellingFormatRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\Otto\Model\Template\PolicyInterface
    {
        return $this->sellingFormatRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\Otto\Model\Template\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingBySellingPolicy($policy->getId());
    }

    protected function delete(\M2E\Otto\Model\Template\PolicyInterface $policy): void
    {
        /** @var \M2E\Otto\Model\Template\SellingFormat $policy */
        $this->sellingFormatRepository->delete($policy);
    }
}
