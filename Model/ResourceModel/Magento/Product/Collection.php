<?php

namespace M2E\Otto\Model\ResourceModel\Magento\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    private bool $listingProductMode = false;
    private \M2E\Otto\Helper\Module\Database\Structure $dbStructureHelper;
    private \M2E\Otto\Helper\Magento\Stock $magentoStockHelper;
    private \M2E\Otto\Helper\Magento\Staging $magentoStagingHelper;

    public function __construct(
        \M2E\Otto\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\Otto\Helper\Magento\Stock $magentoStockHelper,
        \M2E\Otto\Helper\Magento\Staging $magentoStagingHelper,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
        $this->dbStructureHelper = $dbStructureHelper;
        $this->magentoStockHelper = $magentoStockHelper;
        $this->magentoStagingHelper = $magentoStagingHelper;
    }

    public function setListingProductModeOn(): self
    {
        $this->listingProductMode = true;

        $this->_setIdFieldName('id');

        return $this;
    }

    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);

        if ($idsSelect->getPart(\Magento\Framework\DB\Select::HAVING) === []) {
            $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        }

        if (!$this->listingProductMode) {
            $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());
            $idsSelect->limit($limit, $offset);
            $idsSelect->resetJoinLeft();

            return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
        }

        // hack for selecting listing product ids instead entity ids

        $idsSelect->columns('lp.' . $this->getIdFieldName());
        $idsSelect->limit($limit, $offset);

        $data = $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);

        $ids = [];
        foreach ($data as $row) {
            $ids[] = $row[$this->getIdFieldName()];
        }

        return $ids;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        if ($this->_totalRecords === null) {
            $this->_renderFilters();

            if (!$this->listingProductMode) {
                $selectCountSql = $this->getSelectCountSql();
                $selectCountSql->reset(\Magento\Framework\DB\Select::DISTINCT);
                $query = $selectCountSql->__toString();
            } else {
                $countSelect = $this->_getClearSelect();
                $query = $countSelect->columns("COUNT(DISTINCT lp.{$this->getIdFieldName()})")->__toString();
            }

            $this->_totalRecords = $this->getConnection()->fetchOne($query, $this->_bindParams);
        }

        return (int)($this->_totalRecords);
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    protected function _getClearSelect()
    {
        $havingColumns = $this->getHavingColumns();
        $parentSelect = parent::_getClearSelect();

        if (empty($havingColumns)) {
            return $parentSelect;
        }

        foreach ($this->getSelect()->getPart('columns') as $columnData) {
            if (in_array($columnData[2], $havingColumns, true)) {
                $parentSelect->columns([$columnData[2] => $columnData[1]], $columnData[0]);
            }
        }

        return $parentSelect;
    }

    /**
     * @return array
     */
    protected function getHavingColumns()
    {
        $having = $this->getSelect()->getPart('having');

        if (empty($having)) {
            return [];
        }

        $columnsInHaving = [];

        foreach ($having as $havingPart) {
            preg_match_all(
                '/((`{0,1})\w+(`{0,1}))' .
                '( = | > | < | >= | <= | <> | <=> | != | LIKE | NOT | BETWEEN | IS NULL| IS NOT NULL| IN\(.*?\))/i',
                $havingPart,
                $matches
            );

            foreach ($matches[1] as $match) {
                $columnsInHaving[] = trim($match);
            }
        }

        return array_unique($columnsInHaving);
    }

    /**
     * Price Sorting Hack
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if ($attribute === 'min_online_price' || $attribute === 'max_online_price') {
            $this->getSelect()->order($attribute . ' ' . $dir);

            return $this;
        }

        return parent::addAttributeToSort($attribute, $dir);
    }

    public function joinStockItem()
    {
        if ($this->_storeId === null) {
            throw new \M2E\Otto\Model\Exception('Store view was not set.');
        }

        $this->joinTable(
            [
                'cisi' => $this->dbStructureHelper->getTableNameWithPrefix('cataloginventory_stock_item'),
            ],
            'product_id=entity_id',
            [
                'qty' => 'qty',
                'is_in_stock' => 'is_in_stock',
            ],
            [
                'stock_id' => $this->magentoStockHelper->getStockId(),
                'website_id' => $this->magentoStockHelper->getWebsiteId(),
            ],
            'left'
        );

        return $this;
    }

    /**
     * Compatibility with Magento Enterprise (Staging modules) - entity_id column issue
     */
    public function joinTable($table, $bind, $fields = null, $cond = null, $joinType = 'inner')
    {
        if (
            $this->magentoStagingHelper->isInstalled() &&
            $this->magentoStagingHelper->isStagedTable($table, ProductAttributeInterface::ENTITY_TYPE_CODE) &&
            strpos($bind, 'entity_id') !== false
        ) {
            $bind = str_replace(
                'entity_id',
                $this->magentoStagingHelper->getTableLinkField(ProductAttributeInterface::ENTITY_TYPE_CODE),
                $bind
            );
        }

        return parent::joinTable($table, $bind, $fields, $cond, $joinType);
    }

    /**
     * Extension for self::addFieldToFilter() with attribute for sub query
     *
     * @param string $attribute
     * @param mixed $condition
     * @param string $joinType
     *
     * @return string
     * @see self::addFieldToFilter()
     */
    protected function _getAttributeConditionSql($attribute, $condition, $joinType = 'inner')
    {
        if (!empty($condition['raw'])) {
            return $this->_getConditionSql($attribute, $condition);
        }

        return parent::_getAttributeConditionSql($attribute, $condition, $joinType);
    }
}
