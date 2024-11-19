<?php

namespace M2E\Otto\Model\Product;

use M2E\Otto\Model\ResourceModel\Listing as ListingResource;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Product $listingProductResource;
    private ListingProductResource\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Otto\Model\ProductFactory $listingProductFactory;
    private \M2E\Otto\Helper\Data\Cache\Runtime $runtimeCache;
    private \M2E\Otto\Model\ResourceModel\Listing $listingResource;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private ListingProductResource $productResource;
    private \M2E\Otto\Model\ResourceModel\Product\Lock $productLockResource;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product\Lock $productLockResource,
        \M2E\Otto\Model\ResourceModel\Product $productResource,
        \M2E\Otto\Model\ResourceModel\Listing $listingResource,
        \M2E\Otto\Helper\Data\Cache\Runtime $runtimeCache,
        \M2E\Otto\Model\ResourceModel\Product $listingProductResource,
        ListingProductResource\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\ProductFactory $listingProductFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->productLockResource = $productLockResource;
        $this->productResource = $productResource;
        $this->resourceConnection = $resourceConnection;
        $this->listingProductResource = $listingProductResource;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->listingProductFactory = $listingProductFactory;
        $this->runtimeCache = $runtimeCache;
        $this->listingResource = $listingResource;
    }

    public function create(\M2E\Otto\Model\Product $product): void
    {
        $this->listingProductResource->save($product);
    }

    public function save(
        \M2E\Otto\Model\Product $product
    ): \M2E\Otto\Model\Product {
        $this->listingProductResource->save($product);

        return $product;
    }

    public function find(int $id): ?\M2E\Otto\Model\Product
    {
        $listingProduct = $this->listingProductFactory->create();
        $this->listingProductResource->load($listingProduct, $id);

        if ($listingProduct->isObjectNew()) {
            return null;
        }

        return $listingProduct;
    }

    public function get(int $id): \M2E\Otto\Model\Product
    {
        $listingProduct = $this->find($id);
        if ($listingProduct === null) {
            throw new \M2E\Otto\Model\Exception\ListingProductNotFound('Listing product not found.', ['id' => $id]);
        }

        return $listingProduct;
    }

    /**
     * @param $magentoProductId
     * @param array $listingFilters
     * @param array $listingProductFilters
     *
     * @return \M2E\Otto\Model\Product[]
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function getItemsByMagentoProductId(
        int $magentoProductId,
        array $listingFilters = [],
        array $listingProductFilters = []
    ): array {
        $filters = [$listingFilters, $listingProductFilters];
        $cacheKey = __METHOD__ . $magentoProductId . sha1(\M2E\Otto\Helper\Json::encode($filters));
        $cacheValue = $this->runtimeCache->getValue($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $connection = $this->listingProductCollectionFactory->create()->getConnection();

        $simpleProductsSelect = $connection
            ->select()
            ->from(
                ['lp' => $this->listingProductResource->getMainTable()],
                ['id', 'option_id' => new \Zend_Db_Expr('NULL')],
            )
            ->where(
                sprintf(
                    '`%s` = ?',
                    \M2E\Otto\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID,
                ),
                $magentoProductId,
            );

        if (!empty($listingProductFilters)) {
            foreach ($listingProductFilters as $column => $value) {
                if (is_array($value)) {
                    $simpleProductsSelect->where(
                        sprintf('`%s` IN(?)', $column),
                        $value,
                    );
                } else {
                    $simpleProductsSelect->where(
                        sprintf('`%s` = ?', $column),
                        $value,
                    );
                }
            }
        }

        if (!empty($listingFilters)) {
            $simpleProductsSelect->join(
                ['l' => $this->listingResource->getMainTable()],
                sprintf(
                    '`l`.`%s` = `lp`.`%s`',
                    \M2E\Otto\Model\ResourceModel\Listing::COLUMN_ID,
                    \M2E\Otto\Model\ResourceModel\Product::COLUMN_LISTING_ID,
                ),
                [],
            );

            foreach ($listingFilters as $column => $value) {
                if (is_array($value)) {
                    $simpleProductsSelect->where(
                        sprintf('`l`.`%s` IN(?)', $column),
                        $value,
                    );
                } else {
                    $simpleProductsSelect->where(
                        sprintf('`l`.`%s` = ?', $column),
                        $value,
                    );
                }
            }
        }

        $connection = $this->listingProductResource->getConnection();

        $unionSelect = $connection->select()->union([
            $simpleProductsSelect,
        ]);

        $result = [];
        $foundOptionsIds = [];

        foreach ($unionSelect->query()->fetchAll() as $item) {
            $tempListingProductId = $item['id'];

            if (!empty($item['option_id'])) {
                $foundOptionsIds[$tempListingProductId][] = $item['option_id'];
            }

            if (!empty($result[$tempListingProductId])) {
                continue;
            }

            $result[$tempListingProductId] = $this->get((int)$tempListingProductId);
        }

        foreach ($foundOptionsIds as $listingProductId => $optionsIds) {
            /** @var non-empty-list<mixed> $optionsIds */
            if (empty($result[$listingProductId]) || empty($optionsIds)) {
                continue;
            }

            $result[$listingProductId]->setData('found_options_ids', $optionsIds);
        }

        $this->runtimeCache->setValue($cacheKey, $result);

        return array_values($result);
    }

    /**
     * @param int $listingId
     *
     * @return int[]
     */
    public function findMagentoProductIdsByListingId(int $listingId): array
    {
        $collection = $this->listingProductCollectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $collection
            ->addFieldToSelect(ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addFieldToSelect(ListingProductResource::COLUMN_ID) // for load collection
            ->addFieldToFilter(ListingProductResource::COLUMN_LISTING_ID, $listingId)
        ;

        $result = [];
        foreach ($collection->getItems() as $product) {
            $result[] = $product->getMagentoProductId();
        }

        return $result;
    }

    public function delete(\M2E\Otto\Model\Product $listingProduct): void
    {
        $this->listingProductResource->delete($listingProduct);
    }

    /**
     * @return \M2E\Otto\Model\Product[]
     */
    public function findByListing(\M2E\Otto\Model\Listing $listing): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ListingProductResource::COLUMN_LISTING_ID,
            ['eq' => $listing->getId()],
        );

        return array_values($collection->getItems());
    }

    public function findByListingAndMagentoProductId(
        \M2E\Otto\Model\Listing $listing,
        int $magentoProductId
    ): ?\M2E\Otto\Model\Product {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ListingProductResource::COLUMN_LISTING_ID,
            ['eq' => $listing->getId()],
        );
        $collection->addFieldToFilter(
            ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['eq' => $magentoProductId],
        );

        $product = $collection->getFirstItem();
        if ($product->isObjectNew()) {
            return null;
        }

        return $product;
    }

    /**
     * @return \M2E\Otto\Model\Product[]
     */
    public function findByIds(array $listingProductsIds): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ListingProductResource::COLUMN_ID,
            ['in' => $listingProductsIds],
        );

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\Otto\Model\Product[]
     */
    public function findByMagentoProductId(int $magentoProductId): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['eq' => $magentoProductId],
        );

        return array_values($collection->getItems());
    }

    /**
     * @param array $ottoProductsIds
     * @param int $accountId
     * @param int|null $listingId
     *
     * @return \M2E\Otto\Model\Product[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findByOttoProductSKUs(
        array $ottoProductsSKUs,
        int $accountId,
        ?int $listingId = null
    ): array {
        if (empty($ottoProductsSKUs)) {
            return [];
        }

        $collection = $this->listingProductCollectionFactory->create();
        $collection
            ->join(
                ['l' => $this->listingResource->getMainTable()],
                sprintf(
                    '`l`.%s = `main_table`.%s',
                    ListingResource::COLUMN_ID,
                    ListingProductResource::COLUMN_LISTING_ID,
                ),
                [],
            )
            ->addFieldToFilter(
                sprintf('main_table.%s', ListingProductResource::COLUMN_OTTO_PRODUCT_SKU),
                ['in' => $ottoProductsSKUs],
            )
            ->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_ACCOUNT_ID), $accountId);

        if ($listingId !== null) {
            $collection->addFieldToFilter(sprintf('l.%s', ListingResource::COLUMN_ID), $listingId);
        }

        return array_values($collection->getItems());
    }

    public function getCountListedProductsForListing(\M2E\Otto\Model\Listing $listing): int
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection
            ->addFieldToFilter(ListingProductResource::COLUMN_LISTING_ID, $listing->getId())
            ->addFieldToFilter(ListingProductResource::COLUMN_STATUS, \M2E\Otto\Model\Product::STATUS_LISTED);

        return (int)$collection->getSize();
    }

    /**
     * @return \M2E\Otto\Model\Product[]
     */
    public function findStatusListedByListing(\M2E\Otto\Model\Listing $listing): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            ListingProductResource::COLUMN_LISTING_ID,
            ['eq' => $listing->getId()],
        );
        $collection->addFieldToFilter(
            ListingProductResource::COLUMN_STATUS,
            ['eq' => \M2E\Otto\Model\Product::STATUS_LISTED],
        );

        return array_values($collection->getItems());
    }

    public function findListedProductInListingByMagentoProduct(
        int $magentoProductId,
        int $listingId
    ): ?\M2E\Otto\Model\Product {
        $collection = $this->listingProductCollectionFactory->create();

        $collection
            ->addFieldToFilter(
                ListingProductResource::COLUMN_STATUS,
                ['neq' => \M2E\Otto\Model\Product::STATUS_NOT_LISTED],
            )
            ->addFieldToFilter(ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId)
            ->addFieldToFilter(ListingProductResource::COLUMN_LISTING_ID, $listingId);

        $product = $collection->getFirstItem();
        if ($product->isObjectNew()) {
            return null;
        }

        return $product;
    }

    public function setCategoryTemplate(array $productsIds, int $templateCategoryId): void
    {
        if (empty($productsIds)) {
            return;
        }

        $this->listingProductResource
            ->getConnection()
            ->update(
                $this->listingProductResource->getMainTable(),
                [
                    ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID => $templateCategoryId,
                ],
                ['id IN (?)' => $productsIds],
            );
    }

    public function findListedOrLockedProductsBySku(int $id, string $sku): ?\M2E\Otto\Model\Product
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(['p' => $this->productResource->getMainTable()])
            ->joinLeft(
                ['pl' => $this->productLockResource->getMainTable()],
                sprintf(
                    'pl.%s = p.%s AND pl.%s = :initiator',
                    \M2E\Otto\Model\ResourceModel\Product\Lock::COLUMN_PRODUCT_ID,
                    \M2E\Otto\Model\ResourceModel\Product::COLUMN_ID,
                    \M2E\Otto\Model\ResourceModel\Product\Lock::COLUMN_INITIATOR
                ),
                []
            )
            ->where(sprintf(
                'p.%s = :sku OR (p.%s = :sku AND pl.%s IS NOT NULL AND pl.%s != :currentProductId)',
                \M2E\Otto\Model\ResourceModel\Product::COLUMN_OTTO_PRODUCT_SKU,
                \M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_SKU,
                \M2E\Otto\Model\ResourceModel\Product\Lock::COLUMN_ID,
                \M2E\Otto\Model\ResourceModel\Product\Lock::COLUMN_PRODUCT_ID,
            ));

        $result = $connection->fetchAll(
            $select,
            ['sku' => $sku, 'initiator' => 'list', 'currentProductId' => $id]
        );

        if (!empty($result)) {
            return $this->get((int)$result[0]['id']);
        }

        return null;
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     *
     * @return \M2E\Otto\Model\Product[]
     */
    public function massActionSelectedProducts(\Magento\Ui\Component\MassAction\Filter $filter): array
    {
        $collection = $this->listingProductCollectionFactory->create();
        $filter->getCollection($collection);

        return array_values($collection->getItems());
    }

    public function updateLastBlockingErrorDate(array $listingProductIds, \DateTime $dateTime): void
    {
        if (empty($listingProductIds)) {
            return;
        }

        $this->listingProductResource->getConnection()->update(
            $this->listingProductResource->getMainTable(),
            [ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE => $dateTime->format('Y-m-d H:i:s')],
            ['id IN (?)' => $listingProductIds]
        );
    }

    public function findIdsByListingId(int $listingId): array
    {
        if (empty($listingId)) {
            return [];
        }

        $select = $this->listingProductResource->getConnection()
            ->select()
            ->from($this->listingProductResource->getMainTable(), 'id')
            ->where('listing_id = ?', $listingId);

        return array_column($select->query()->fetchAll(), 'id');
    }
}
