<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Listing;

/**
 * @method \M2E\Otto\Model\Listing[] getItems()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    private \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Helper\Module\Database\Structure $moduleDatabaseStructure,
        \M2E\Core\Helper\Magento\Staging $magentoStagingHelper,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $magentoStagingHelper,
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Listing::class,
            \M2E\Otto\Model\ResourceModel\Listing::class
        );
    }

    public function addProductsTotalCount(): \M2E\Otto\Model\ResourceModel\Listing\Collection
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToSelect(\M2E\Otto\Model\ResourceModel\Product::COLUMN_LISTING_ID);
        $collection->addExpressionFieldToSelect(
            'products_total_count',
            'COUNT({{id}})',
            ['id' => \M2E\Otto\Model\ResourceModel\Product::COLUMN_ID]
        );
        $collection->getSelect()->group(\M2E\Otto\Model\ResourceModel\Product::COLUMN_LISTING_ID);

        $this->getSelect()->joinLeft(
            ['t' => $collection->getSelect()],
            'main_table.id=t.listing_id',
            [
                'products_total_count' => 'products_total_count',
            ]
        );

        return $this;
    }
}
