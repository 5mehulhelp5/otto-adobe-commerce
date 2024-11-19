<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other;

class Updater
{
    private Repository $unmanagedRepository;
    private \M2E\Otto\Model\Listing\Other\MappingService $mappingService;
    /** @var \M2E\Otto\Model\Listing\Other\Updater\ServerToOttoProductConverterFactory */
    private Updater\ServerToOttoProductConverterFactory $otherConverterFactory;
    private \M2E\Otto\Model\Account $account;
    private \M2E\Otto\Model\Product\Repository $listingProductRepository;
    private \M2E\Otto\Model\Listing\OtherFactory $otherFactory;

    public function __construct(
        \M2E\Otto\Model\Account $account,
        \M2E\Otto\Model\Listing\OtherFactory $otherFactory,
        \M2E\Otto\Model\Listing\Other\Repository $unmanagedRepository,
        \M2E\Otto\Model\Product\Repository $listingProductRepository,
        \M2E\Otto\Model\Listing\Other\Updater\ServerToOttoProductConverterFactory $otherConverterFactory,
        \M2E\Otto\Model\Listing\Other\MappingService $mappingService
    ) {
        $this->unmanagedRepository = $unmanagedRepository;
        $this->mappingService = $mappingService;
        $this->otherConverterFactory = $otherConverterFactory;
        $this->listingProductRepository = $listingProductRepository;
        $this->otherFactory = $otherFactory;
        $this->account = $account;
    }

    public function process(array $partialData): ?OttoProductCollection
    {
        if (empty($partialData)) {
            return null;
        }

        $converter = $this->otherConverterFactory->create($this->account);

        $ottoProductsCollection = $converter->convert($partialData);

        $itemsCollection = $this->removeExistInListingProduct($ottoProductsCollection);

        $this->processExist($ottoProductsCollection);
        $unmanagedItems = $this->processNew($ottoProductsCollection);

        // remove not exist

        $this->autoMapping($unmanagedItems);

        return $itemsCollection;
    }

    private function removeExistInListingProduct(OttoProductCollection $collection): OttoProductCollection
    {
        $existInListingCollection = new \M2E\Otto\Model\Listing\Other\OttoProductCollection();

        if ($collection->empty()) {
            return $existInListingCollection;
        }

        $existed = $this->listingProductRepository->findByOttoProductSKUs(
            $collection->getProductsSKUs(),
            $this->account->getId()
        );

        foreach ($existed as $product) {
            $existInListingCollection->add($collection->get($product->getOttoProductSku()));

            $collection->remove($product->getOttoProductSKU());
        }

        return $existInListingCollection;
    }

    private function processExist(OttoProductCollection $collection): void
    {
        if ($collection->empty()) {
            return;
        }

        $existProducts = $this->unmanagedRepository->findByProductSKUs(
            $collection->getProductsSKUs(),
            $this->account->getId()
        );

        foreach ($existProducts as $existProduct) {
            if (!$collection->has($existProduct->getSKU())) {
                continue;
            }

            $new = $collection->get($existProduct->getSKU());
            $collection->remove($existProduct->getSKU());

            if ($existProduct->getTitle() !== $new->getTitle()) {
                $existProduct->setTitle($new->getTitle());
            }

            if ($existProduct->getQty() !== $new->getQty()) {
                $existProduct->setQty($new->getQty());
            }

            if ($existProduct->getPrice() !== $new->getPrice()) {
                $existProduct->setPrice($new->getPrice());
            }

            if ($existProduct->getOttoProductMoin() !== $new->getMoin()) {
                $existProduct->setOttoProductMoin($new->getMoin());
            }

            if ($existProduct->getShippingProfileId() !== $new->getShippingProfileId()) {
                $existProduct->setShippingProfileId($new->getShippingProfileId());
            }

            if ($existProduct->getStatus() !== $new->getStatus()) {
                $existProduct->setStatus($new->getStatus());
            }

            if ($existProduct->getQtyActualizeDate() !== $new->getQtyActualizeDate()) {
                $existProduct->setQtyActualizeDate($new->getQtyActualizeDate());
            }

            if ($existProduct->getPriceActualizeDate() !== $new->getPriceActualizeDate()) {
                $existProduct->setPriceActualizeDate($new->getPriceActualizeDate());
            }

            if ($existProduct->getOttoProductUrl() !== $new->getProductUrl()) {
                $existProduct->setOttoProductUrl($new->getProductUrl());
            }

            if ($existProduct->isProductIncomplete() !== $new->isChannelProductInComplete()) {
                $existProduct->makeProductIncomplete($new->isChannelProductInComplete());
            }

            $this->unmanagedRepository->save($existProduct);
        }
    }

    /**
     * @param \M2E\Otto\Model\Listing\Other\OttoProductCollection $collection
     *
     * @return \M2E\Otto\Model\Listing\Other[]
     */
    private function processNew(OttoProductCollection $collection): array
    {
        $result = [];
        foreach ($collection->getAll() as $item) {
            $other = $this->otherFactory->create();
            $other->init(
                $this->account,
                $item->getProductReference(),
                $item->getEan(),
                $item->getMoin(),
                $item->getSku(),
                $item->getStatus(),
                $item->getTitle(),
                $item->getCurrency(),
                $item->getPrice(),
                $item->getVat(),
                $item->getQty(),
                $item->getMedia(),
                $item->getCategory(),
                $item->getBrandId(),
                $item->getDelivery(),
                $item->getProductUrl(),
                $item->getQtyActualizeDate(),
                $item->getPriceActualizeDate(),
                $item->isChannelProductInComplete(),
                $item->getShippingProfileId()
            );

            $this->unmanagedRepository->create($other);

            $result[] = $other;
        }

        return $result;
    }

    /**
     * @param \M2E\Otto\Model\Listing\Other[] $otherListings
     */
    private function autoMapping(array $otherListings): void
    {
        $this->mappingService->autoMapOtherListingsProducts($otherListings);
    }
}
