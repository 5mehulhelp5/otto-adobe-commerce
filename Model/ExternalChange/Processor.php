<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ExternalChange;

class Processor
{
    private \M2E\Otto\Model\ExternalChange\Repository $externalChangesRepository;
    private \M2E\Otto\Model\ExternalChangeFactory $externalChangeFactory;
    private \M2E\Otto\Model\Product\Repository $productRepository;
    private \M2E\Otto\Model\Listing\Other\Repository $otherRepository;
    private \M2E\Otto\Model\Listing\Other\DeleteService $unmanagedProductDeleteService;
    private \M2E\Otto\Model\InstructionService $instructionService;
    private \M2E\Otto\Model\Listing\LogService $logService;

    private int $logActionId;

    public function __construct(
        \M2E\Otto\Model\ExternalChange\Repository $externalChangeRepository,
        \M2E\Otto\Model\ExternalChangeFactory $externalChangeFactory,
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\Listing\Other\Repository $otherRepository,
        \M2E\Otto\Model\Listing\Other\DeleteService $unmanagedProductDeleteService,
        \M2E\Otto\Model\InstructionService $instructionService,
        \M2E\Otto\Model\Listing\LogService $logService
    ) {
        $this->externalChangesRepository = $externalChangeRepository;
        $this->externalChangeFactory = $externalChangeFactory;
        $this->productRepository = $productRepository;
        $this->otherRepository = $otherRepository;
        $this->unmanagedProductDeleteService = $unmanagedProductDeleteService;
        $this->instructionService = $instructionService;
        $this->logService = $logService;
    }

    public function processReceivedProducts(
        \M2E\Otto\Model\Account $account,
        \M2E\Otto\Model\Listing\Other\OttoProductCollection $productCollection
    ): void {
        foreach ($productCollection->getAll() as $item) {
            $externalChange = $this->externalChangeFactory->create();
            $externalChange->init(
                $account,
                $item->getSku(),
            );

            $this->externalChangesRepository->create($externalChange);
        }
    }

    public function processDeletedProducts(
        \M2E\Otto\Model\Account $account,
        \DateTime $inventorySyncProcessingStartDate
    ): void {
        $this->processNotReceivedProducts($account, $inventorySyncProcessingStartDate);
        $this->removeNotReceivedOtherListings($account);

        $this->externalChangesRepository
            ->removeAllByAccount($account->getId());
    }

    private function processNotReceivedProducts(
        \M2E\Otto\Model\Account $account,
        \DateTime $inventorySyncProcessingStartDate
    ): void {
        $removedProducts = $this->productRepository->findRemovedFromChannel(
            $account->getId(),
            $inventorySyncProcessingStartDate
        );

        foreach ($removedProducts as $product) {
            $product->setStatusNotListed(\M2E\Otto\Model\Product::STATUS_CHANGER_COMPONENT);

            $this->productRepository->save($product);

            $this->logService->addRecordToProduct(
                \M2E\Otto\Model\Listing\Log\Record::createSuccess(
                    (string)__('Product was deleted and is no longer available on the channel'),
                ),
                $product,
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                \M2E\Otto\Model\Listing\Log::ACTION_CHANNEL_CHANGE,
                $this->getLogActionId(),
            );

            $this->instructionService->create(
                (int)$product->getId(),
                \M2E\Otto\Model\Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                'channel_changes_synchronization',
                80,
            );
        }
    }

    private function removeNotReceivedOtherListings(
        \M2E\Otto\Model\Account $account
    ): void {
        $otherListings = $this->otherRepository->findRemovedFromChannel($account->getId());

        foreach ($otherListings as $other) {
            $this->unmanagedProductDeleteService->process($other);
        }
    }

    private function getLogActionId(): int
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->logActionId ?? ($this->logActionId = $this->logService->getNextActionId());
    }
}
