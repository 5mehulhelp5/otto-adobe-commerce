<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing;

use M2E\Otto\Model\Listing\LogService;

class DeleteService
{
    private \M2E\Otto\Model\Processing\DeleteService $processingDeleteService;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\Product\DeleteService $productDeleteService;
    private \M2E\Otto\Model\Listing\LogService $listingLogService;
    private \M2E\Otto\Model\Product\Repository $productRepository;
    private \M2E\Otto\Model\Listing\Wizard\DeleteService $wizardDeleteService;

    public function __construct(
        \M2E\Otto\Model\Processing\DeleteService $processingDeleteService,
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Model\Product\DeleteService $productDeleteService,
        LogService $listingLogService,
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\Listing\Wizard\DeleteService $wizardDeleteService
    ) {
        $this->processingDeleteService = $processingDeleteService;
        $this->listingRepository = $listingRepository;
        $this->productDeleteService = $productDeleteService;
        $this->listingLogService = $listingLogService;
        $this->productRepository = $productRepository;
        $this->wizardDeleteService = $wizardDeleteService;
    }

    public function isAllowed(\M2E\Otto\Model\Listing $listing): bool
    {
        return $this->productRepository->getCountListedProductsForListing($listing) === 0
            && !$this->listingRepository->hasProductsInSomeAction($listing);
    }

    public function process(\M2E\Otto\Model\Listing $listing, bool $isForce = false): void
    {
        if (!$isForce && !$this->isAllowed($listing)) {
            return;
        }

        $this->processingDeleteService->deleteByObjAndObjId(
            \M2E\Otto\Model\Listing::LOCK_NICK,
            $listing->getId(),
        );

        $this->deleteProducts($listing);
        $this->wizardDeleteService->removeByListing($listing);

        $this->listingLogService->addListing(
            $listing,
            \M2E\Core\Helper\Data::INITIATOR_UNKNOWN,
            \M2E\Otto\Model\Listing\Log::ACTION_DELETE_LISTING,
            null,
            (string)__('Listing was deleted'),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO
        );

        $this->listingRepository->remove($listing);
    }

    private function deleteProducts(\M2E\Otto\Model\Listing $listing): void
    {
        foreach ($listing->getProducts() as $listingProduct) {
            $this->productDeleteService->process($listingProduct);
        }
    }
}
