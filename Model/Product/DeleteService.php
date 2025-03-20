<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

class DeleteService
{
    private \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Otto\Model\Product\Repository $listingProductRepository;
    private \M2E\Otto\Model\ScheduledAction\Repository $scheduledActionRepository;
    private \M2E\Otto\Model\Instruction\Repository $instructionRepository;
    private \M2E\Otto\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Otto\Model\Product\Repository $listingProductRepository,
        \M2E\Otto\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\Otto\Model\Instruction\Repository $instructionRepository,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        $this->tagBuffer = $tagBuffer;
        $this->listingProductRepository = $listingProductRepository;
        $this->scheduledActionRepository = $scheduledActionRepository;
        $this->instructionRepository = $instructionRepository;
        $this->listingLogService = $listingLogService;
    }

    public function process(\M2E\Otto\Model\Product $listingProduct): void
    {
        $this->removeTags($listingProduct);

        $this->removeScheduledActions($listingProduct);
        $this->removeInstructions($listingProduct);

        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Core\Helper\Data::INITIATOR_UNKNOWN,
            \M2E\Otto\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_LISTING,
            $this->listingLogService->getNextActionId(),
            (string)__('Product was Deleted'),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO,
        );

        $this->listingProductRepository->delete($listingProduct);
    }

    private function removeTags(\M2E\Otto\Model\Product $listingProduct): void
    {
        $this->tagBuffer->removeAllTags($listingProduct);
        $this->tagBuffer->flush();
    }

    private function removeScheduledActions(\M2E\Otto\Model\Product $listingProduct): void
    {
        $scheduledAction = $this->scheduledActionRepository->findByListingProductId($listingProduct->getId());
        if ($scheduledAction !== null) {
            $this->scheduledActionRepository->remove($scheduledAction);
        }
    }

    private function removeInstructions(\M2E\Otto\Model\Product $listingProduct): void
    {
        $this->instructionRepository->removeByListingProduct($listingProduct->getId());
    }
}
