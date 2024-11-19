<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping;

class DeleteService extends \M2E\Otto\Model\Template\AbstractDeleteService
{
    private \M2E\Otto\Model\Template\Shipping\Repository $shippingRepository;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\Template\Shipping\ShippingService $shippingService;

    public function __construct(
        \M2E\Otto\Model\Template\Shipping\ShippingService $shippingService,
        \M2E\Otto\Model\Template\Shipping\Repository $shippingRepository,
        \M2E\Otto\Model\Listing\Repository $listingRepository
    ) {
        $this->shippingService = $shippingService;
        $this->shippingRepository = $shippingRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function loadPolicy(int $id): \M2E\Otto\Model\Template\PolicyInterface
    {
        return $this->shippingRepository->get($id);
    }

    protected function isUsedPolicy(\M2E\Otto\Model\Template\PolicyInterface $policy): bool
    {
        return $this->listingRepository->isExistListingByShippingPolicy($policy->getId());
    }

    protected function delete(\M2E\Otto\Model\Template\PolicyInterface $policy): void
    {
        /** @var \M2E\Otto\Model\Template\Shipping $policy */
        $this->shippingRepository->delete($policy);

        if (!$policy->getShippingProfileId()) {
            return;
        }

        try {
            $this->shippingService->deleteOnChannel($policy);
        } catch (\M2E\Otto\Model\Exception $e) {
        }
    }
}
