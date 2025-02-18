<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other;

use M2E\Otto\Model\ResourceModel\Listing\Other as ListingOtherResource;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use M2E\Otto\Model\ResourceModel\ExternalChange as ExternalChangeResource;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory;
    private \M2E\Otto\Model\ResourceModel\Listing\Other $resource;
    private \M2E\Otto\Model\Listing\OtherFactory $objectFactory;
    private \M2E\Otto\Helper\Module\Database\Structure $dbStructureHelper;
    private \M2E\Otto\Model\ResourceModel\ExternalChange $externalChangeResource;

    public function __construct(
        \M2E\Otto\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\Otto\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory,
        \M2E\Otto\Model\ResourceModel\Listing\Other $resource,
        \M2E\Otto\Model\ResourceModel\ExternalChange $externalChangeResource,
        \M2E\Otto\Model\Listing\OtherFactory $objectFactory
    ) {
        $this->dbStructureHelper = $dbStructureHelper;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->objectFactory = $objectFactory;
        $this->externalChangeResource = $externalChangeResource;
    }

    public function createCollection(): \M2E\Otto\Model\ResourceModel\Listing\Other\Collection
    {
        return $this->collectionFactory->create();
    }

    public function create(\M2E\Otto\Model\Listing\Other $other): void
    {
        $this->resource->save($other);
    }

    public function save(\M2E\Otto\Model\Listing\Other $listingOther): void
    {
        $this->resource->save($listingOther);
    }

    /**
     * @throws \M2E\Otto\Model\Exception
     */
    public function get(int $id): \M2E\Otto\Model\Listing\Other
    {
        $obj = $this->objectFactory->create();
        $this->resource->load($obj, $id);

        if ($obj->isObjectNew()) {
            throw new \M2E\Otto\Model\Exception("Object by id $id not found.");
        }

        return $obj;
    }

    public function remove(\M2E\Otto\Model\Listing\Other $other): void
    {
        $this->resource->delete($other);
    }

    /**
     * @param int $id
     *
     * @return \M2E\Otto\Model\Listing\Other|null
     */
    public function findById(int $id): ?\M2E\Otto\Model\Listing\Other
    {
        $obj = $this->objectFactory->create();
        $this->resource->load($obj, $id);

        if ($obj->isObjectNew()) {
            return null;
        }

        return $obj;
    }

    /**
     * @return \M2E\Otto\Model\Listing\Other[]
     */
    public function findByIds(array $ids): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_ID,
            ['in' => $ids],
        );

        return array_values($collection->getItems());
    }

    /**
     * @param array $productsSKUs
     * @param int $accountId
     *
     * @return \M2E\Otto\Model\Listing\Other[]
     */
    public function findByProductSKUs(array $productsSKUs, int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ListingOtherResource::COLUMN_SKU,
                ['in' => $productsSKUs],
            )
            ->addFieldToFilter(ListingOtherResource::COLUMN_ACCOUNT_ID, $accountId);

        return array_values($collection->getItems());
    }

    public function findByProductEANs(array $productsEANs, int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ListingOtherResource::COLUMN_EAN,
                ['in' => $productsEANs],
            )
            ->addFieldToFilter(ListingOtherResource::COLUMN_ACCOUNT_ID, $accountId);

        return array_values($collection->getItems());
    }

    public function removeByAccountId(int $accountId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            ['account_id = ?' => $accountId],
        );
    }

    public function getBySku(string $sku): ?\M2E\Otto\Model\Listing\Other
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('sku', $sku);

        $item = $collection->getFirstItem();
        if (!$item->getId()) {
            return null;
        }

        return $item;
    }

    public function findByMagentoProductId(int $magentoProductId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return array_values($collection->getItems());
    }

    public function isExistForAccountId(int $accountId): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_ACCOUNT_ID, $accountId);

        return (int)$collection->getSize() > 0;
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param int $accountId
     *
     * @return \M2E\Otto\Model\Listing\Other[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findForMovingByMassActionSelectedProducts(MassActionFilter $filter, int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $filter->getCollection($collection);

        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true]
        );

        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_ACCOUNT_ID,
            $accountId
        );

        return array_values($collection->getItems());
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param int $accountId
     *
     * @return \M2E\Otto\Model\Listing\Other[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findForAutoMappingByMassActionSelectedProducts(MassActionFilter $filter, int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $filter->getCollection($collection);

        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['null' => true]
        );

        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_ACCOUNT_ID,
            $accountId
        );

        return array_values($collection->getItems());
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param int $accountId
     *
     * @return \M2E\Otto\Model\Listing\Other[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findForUnmappingByMassActionSelectedProducts(MassActionFilter $filter, int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $filter->getCollection($collection);

        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true]
        );

        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_ACCOUNT_ID,
            $accountId
        );

        return array_values($collection->getItems());
    }

    /**
     * @param array $ids
     * @param int $accountId
     *
     * @return array|bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function findPrepareMoveToListingByIds(array $ids, int $accountId)
    {
        $listingOtherCollection = $this->collectionFactory->create();
        $listingOtherCollection->addFieldToFilter('id', ['in' => $ids]);
        $listingOtherCollection->addFieldToFilter(
            \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true]
        );

        $listingOtherCollection->getSelect()->join(
            ['cpe' => $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity')],
            'magento_product_id = cpe.entity_id'
        );

        $listingOtherCollection->addFieldToFilter(
            ListingOtherResource::COLUMN_ACCOUNT_ID,
            $accountId
        );

        return $listingOtherCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->group(['account_id'])
            ->columns(['account_id'])
            ->query()
            ->fetch();
    }

    /**
     * @param int $accountId
     *
     * @return \M2E\Otto\Model\Listing\Other[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findRemovedFromChannel(int $accountId): array
    {
        $joinConditions = [
            sprintf(
                '`ec`.%s = `main_table`.%s',
                ExternalChangeResource::COLUMN_SKU,
                ListingOtherResource::COLUMN_SKU,
            ),
            sprintf(
                '`ec`.%s = `main_table`.%s',
                ExternalChangeResource::COLUMN_ACCOUNT_ID,
                ListingOtherResource::COLUMN_ACCOUNT_ID,
            )
        ];

        $collection = $this->collectionFactory->create();
        $collection->joinLeft(
            [
                'ec' => $this->externalChangeResource->getMainTable(),
            ],
            implode(' AND ', $joinConditions),
            [],
        );

        $collection
            ->addFieldToFilter(sprintf('main_table.%s', ListingOtherResource::COLUMN_ACCOUNT_ID), $accountId)
            ->addFieldToFilter('ec.id', ['null' => true]);

        return array_values($collection->getItems());
    }
}
