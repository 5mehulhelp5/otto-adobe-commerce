<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing;

use M2E\Otto\Model\ResourceModel\Listing as ListingResource;

class Repository
{
    use \M2E\Otto\Model\CacheTrait;

    private \M2E\Otto\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Listing $listingResource;
    private \M2E\Otto\Model\ListingFactory $listingFactory;
    private \M2E\Otto\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Listing $listingResource,
        \M2E\Otto\Model\ListingFactory $listingFactory,
        \M2E\Otto\Helper\Data\Cache\Permanent $cache
    ) {
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingResource = $listingResource;
        $this->listingFactory = $listingFactory;
        $this->cache = $cache;
    }

    public function getListingsCount(): int
    {
        return $this->listingCollectionFactory->create()
                                              ->getSize();
    }

    public function get(int $id): \M2E\Otto\Model\Listing
    {
        $listing = $this->find($id);
        if ($listing === null) {
            throw new \M2E\Otto\Model\Exception\Logic('Listing does not exist.');
        }

        return $listing;
    }

    public function find(int $id): ?\M2E\Otto\Model\Listing
    {
        $listing = $this->listingFactory->create();

        $cacheData = $this->cache->getValue($this->makeCacheKey($listing, $id));
        if (!empty($cacheData)) {
            $this->initializeFromCache($listing, $cacheData);

            return $listing;
        }

        $this->listingResource->load($listing, $id);

        if ($listing->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($listing, $id),
            $this->getCacheDate($listing),
            [],
            60 * 60
        );

        return $listing;
    }

    public function save(\M2E\Otto\Model\Listing $listing): void
    {
        $this->listingResource->save($listing);
        $this->cache->removeValue($this->makeCacheKey($listing, $listing->getId()));
    }

    public function remove(\M2E\Otto\Model\Listing $listing): void
    {
        $this->listingResource->delete($listing);
        $this->cache->removeValue($this->makeCacheKey($listing, $listing->getId()));
    }

    public function isExistListingByDescriptionPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID, $policyId);
    }

    public function isExistListingBySellingPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID, $policyId);
    }

    public function isExistListingByShippingPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_SHIPPING_ID, $policyId);
    }

    public function isExistListingBySyncPolicy(int $policyId): bool
    {
        return $this->isExistListingByPolicy(ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID, $policyId);
    }

    private function isExistListingByPolicy(
        string $columnName,
        int $policyId
    ): bool {
        $listingCollection = $this->listingCollectionFactory->create();
        $listingCollection->addFieldToFilter($columnName, ['eq' => $policyId]);

        return $listingCollection->getSize() !== 0;
    }
}
