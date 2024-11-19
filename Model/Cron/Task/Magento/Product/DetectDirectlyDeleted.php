<?php

namespace M2E\Otto\Model\Cron\Task\Magento\Product;

use M2E\Otto\Model\ResourceModel\Listing\Other as OtherResource;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;

class DetectDirectlyDeleted extends \M2E\Otto\Model\Cron\AbstractTask
{
    public const NICK = 'magento/product/detect_directly_deleted';

    private ListingProductResource\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Otto\Helper\Module\Database\Structure $dbStructureHelper;
    private \M2E\Otto\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct;
    private \M2E\Otto\Model\Listing\Other\UnmapDeletedProduct $unmanagedUnmapDeletedProduct;
    private \M2E\Otto\Model\ResourceModel\Listing\Other\CollectionFactory $unmanagedCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Listing\Other\CollectionFactory $unmanagedCollectionFactory,
        \M2E\Otto\Model\Listing\Other\UnmapDeletedProduct $unmanagedUnmapDeletedProduct,
        \M2E\Otto\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct,
        \M2E\Otto\Helper\Module\Database\Structure $dbStructureHelper,
        ListingProductResource\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Cron\Manager $cronManager,
        \M2E\Otto\Model\Synchronization\LogService $syncLogger,
        \M2E\Otto\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Otto\Helper\Factory $helperFactory,
        \M2E\Otto\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $helperFactory,
            $taskRepo,
            $resource
        );
        $this->unmanagedCollectionFactory = $unmanagedCollectionFactory;
        $this->unmanagedUnmapDeletedProduct = $unmanagedUnmapDeletedProduct;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->listingRemoveDeletedProduct = $listingRemoveDeletedProduct;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions()
    {
        $this->deleteListingsProducts();
        $this->unmapUnmanagedProducts();
    }

    private function deleteListingsProducts(): void
    {
        $collection = $this->listingProductCollectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns(
            ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID
        );
        $collection->getSelect()->distinct();

        $entityTableName = $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()->joinLeft(
            ['cpe' => $entityTableName],
            sprintf(
                'cpe.entity_id = `main_table`.%s',
                ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID
            ),
            []
        );

        $collection->getSelect()->where('cpe.entity_id IS NULL');
        $collection->getSelect()->limit(100);

        $tempProductsIds = [];
        $rows = $collection->toArray();

        foreach ($rows['items'] as $row) {
            if (in_array((int)$row[ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID], $tempProductsIds)) {
                continue;
            }

            $tempProductsIds[] = (int)$row[ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID];

            $this->listingRemoveDeletedProduct->process((int)$row[ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID]);
        }
    }

    private function unmapUnmanagedProducts(): void
    {
        $collection = $this->unmanagedCollectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns(
            OtherResource::COLUMN_MAGENTO_PRODUCT_ID
        );
        $collection->addFieldToFilter(OtherResource::COLUMN_MAGENTO_PRODUCT_ID, ['notnull' => true]);
        $collection->getSelect()->distinct();

        $entityTableName = $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()->joinLeft(
            ['cpe' => $entityTableName],
            sprintf(
                'cpe.entity_id = `main_table`.%s',
                OtherResource::COLUMN_MAGENTO_PRODUCT_ID
            ),
            []
        );
        $collection->getSelect()->where('cpe.entity_id IS NULL');

        $rows = $collection->toArray();

        foreach ($rows['items'] as $row) {
            $this->unmanagedUnmapDeletedProduct->process($row[OtherResource::COLUMN_MAGENTO_PRODUCT_ID]);
        }
    }
}
