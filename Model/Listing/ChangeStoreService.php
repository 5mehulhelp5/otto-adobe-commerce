<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing;

class ChangeStoreService
{
    private \M2E\Otto\Model\Product\Repository $productRepository;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\InstructionService $instructionService;
    private \M2E\Otto\Model\Listing\LogService $logService;
    private \M2E\Core\Helper\Magento\Store $storeHelper;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Model\InstructionService $instructionService,
        \M2E\Otto\Model\Listing\LogService $logService,
        \M2E\Core\Helper\Magento\Store $storeHelper
    ) {
        $this->productRepository = $productRepository;
        $this->listingRepository = $listingRepository;
        $this->instructionService = $instructionService;
        $this->logService = $logService;
        $this->storeHelper = $storeHelper;
    }

    public function change(\M2E\Otto\Model\Listing $listing, int $storeId): void
    {
        $prevStoreId = $listing->getStoreId();
        $this->updateStoreViewInListing($listing, $storeId);
        $this->addInstruction($listing->getId());
        $this->addChangeLog($listing, $prevStoreId, $storeId);
    }

    private function updateStoreViewInListing(\M2E\Otto\Model\Listing $listing, int $storeId): void
    {
        $listing->setStoreId($storeId);
        $this->listingRepository->save($listing);
    }

    private function addInstruction(int $listingId): void
    {
        $listingProductInstructionsData = [];

        foreach ($this->productRepository->findIdsByListingId($listingId) as $itemId) {
            $listingProductInstructionsData[] = [
                'listing_product_id' => $itemId,
                'type' => \M2E\Otto\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
                'initiator' => \M2E\Otto\Model\Listing::INSTRUCTION_INITIATOR_CHANGED_LISTING_STORE_VIEW,
                'priority' => 20,
            ];
        }

        $this->instructionService->createBatch($listingProductInstructionsData);
    }

    private function addChangeLog(\M2E\Otto\Model\Listing $listing, int $prevStoreId, int $storeId): void
    {
        $this->logService->addRecordToListing(
            $this->prepareChangeRecord($prevStoreId, $storeId),
            $listing,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\Otto\Model\Listing\Log::ACTION_EDIT_LISTING_SETTINGS,
            null
        );
    }

    private function prepareChangeRecord(int $prevStoreId, int $storeId): \M2E\Otto\Model\Listing\Log\Record
    {
        return new \M2E\Otto\Model\Listing\Log\Record(
            (string)__(
                'The Store View for this listing was updated from \'%1\' to \'%2\'.',
                $this->storeHelper->getStoreNameById($prevStoreId),
                $this->storeHelper->getStoreNameById($storeId)
            ),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO
        );
    }
}
