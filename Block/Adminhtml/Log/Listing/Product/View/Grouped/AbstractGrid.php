<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Log\Listing\Product\View\Grouped;

use M2E\Otto\Block\Adminhtml\Log\Listing\View;

abstract class AbstractGrid extends \M2E\Otto\Block\Adminhtml\Log\Listing\Product\AbstractGrid
{
    protected $nestedLogs = [];
    private \M2E\Otto\Model\ResourceModel\Listing\Log\CollectionFactory $listingLogCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Listing\Log\CollectionFactory $listingLogCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Account $accountResource,
        \M2E\Otto\Helper\Module\Log $logHelper,
        \M2E\Otto\Model\Config\Manager $config,
        \M2E\Otto\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Otto\Helper\View $viewHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Otto\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct(
            $accountResource,
            $config,
            $wrapperCollectionFactory,
            $customCollectionFactory,
            $resourceConnection,
            $viewHelper,
            $context,
            $backendHelper,
            $dataHelper,
            $data,
        );

        $this->listingLogCollectionFactory = $listingLogCollectionFactory;
    }

    protected function getViewMode()
    {
        return View\Switcher::VIEW_MODE_GROUPED;
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->getColumn('description')->setData('sortable', false);

        return $this;
    }

    protected function _prepareCollection()
    {
        $logCollection = $this->listingLogCollectionFactory->create();

        $this->applyFilters($logCollection);

        $logCollection->getSelect()
                      ->order(new \Zend_Db_Expr('main_table.id DESC'))
                      ->limit(1, $this->getMaxLastHandledRecordsCount() - 1);

        $lastAllowedLog = $logCollection->getFirstItem();

        if ($lastAllowedLog->getId() !== null) {
            $logCollection->getSelect()->limit($this->getMaxLastHandledRecordsCount());
            $this->addMaxAllowedLogsCountExceededNotification($lastAllowedLog->getCreateDate());
        } else {
            $logCollection->getSelect()
                          ->reset(\Magento\Framework\DB\Select::ORDER)
                          ->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)
                          ->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        }

        $groupedCollection = $this->wrapperCollectionFactory->create();
        $groupedCollection->setConnection($this->resourceConnection->getConnection());
        $groupedCollection->getSelect()->reset()->from(
            ['main_table' => $logCollection->getSelect()],
            [
                'id' => 'main_table.id',
                self::LISTING_PRODUCT_ID_FIELD => 'main_table.' . self::LISTING_PRODUCT_ID_FIELD,
                self::LISTING_ID_FIELD => 'main_table.' . self::LISTING_ID_FIELD,
                'product_id' => 'main_table.product_id',
                'action_id' => 'main_table.action_id',
                'action' => 'main_table.action',
                'listing_title' => 'main_table.listing_title',
                'product_title' => 'main_table.product_title',
                'initiator' => 'main_table.initiator',
                'additional_data' => 'main_table.additional_data',
                'create_date' => new \Zend_Db_Expr('MAX(main_table.create_date)'),
                'description' => new \Zend_Db_Expr('GROUP_CONCAT(main_table.description)'),
                'type' => new \Zend_Db_Expr('MAX(main_table.type)'),
                'nested_log_ids' => new \Zend_Db_Expr('GROUP_CONCAT(main_table.id)'),
            ]
        );

        $groupedCollection->getSelect()->group(['listing_product_id', 'action_id']);

        $resultCollection = $this->wrapperCollectionFactory->create();
        $resultCollection->setConnection($this->resourceConnection->getConnection());
        $resultCollection->getSelect()->reset()->from(
            ['main_table' => $groupedCollection->getSelect()]
        );

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        if (!$this->getCollection()->getSize()) {
            return parent::_afterLoadCollection();
        }

        $logCollection = $this->listingLogCollectionFactory->create();

        $logCollection->getSelect()
                      ->reset(\Magento\Framework\DB\Select::COLUMNS)
                      ->columns([
                          'id',
                          self::LISTING_PRODUCT_ID_FIELD,
                          self::LISTING_ID_FIELD,
                          'action_id',
                          'description',
                          'type',
                          'create_date',
                      ])
                      ->order(new \Zend_Db_Expr('id DESC'));

        $nestedLogsIds = [];
        foreach ($this->getCollection()->getItems() as $log) {
            $nestedLogsIds[] = new \Zend_Db_Expr($log->getNestedLogIds());
        }

        $logCollection->getSelect()->where(
            new \Zend_Db_Expr('main_table.id IN (?)'),
            $nestedLogsIds
        );

        foreach ($logCollection->getItems() as $log) {
            $this->nestedLogs[$this->getLogHash($log)][] = $log;
        }

        $sortOrder = \M2E\Otto\Block\Adminhtml\Log\Grid\LastActions::$actionsSortOrder;

        foreach ($this->nestedLogs as &$logs) {
            usort($logs, function ($a, $b) use ($sortOrder) {
                return $sortOrder[$a['type']] <=> $sortOrder[$b['type']];
            });
        }

        return parent::_afterLoadCollection();
    }

    public function callbackColumnDescription($value, $row, $column, $isExport)
    {
        $description = '';
        $nestedLogs = $this->nestedLogs[$this->getLogHash($row)];

        /** @var \M2E\Otto\Model\Listing\Log $log */
        foreach ($nestedLogs as $log) {
            $messageType = '';
            $createDate = '';

            if (count($nestedLogs) > 1) {
                $messageType = $this->callbackColumnType(
                    '[' . $this->_getLogTypeList()[$log->getType()] . ']',
                    $log,
                    $column,
                    $isExport
                );
                $createDate = $this->_localeDate->formatDate($log->getCreateDate(), \IntlDateFormatter::MEDIUM, true);
            }

            $logDescription = parent::callbackColumnDescription(
                $log->getData($column->getIndex()),
                $log,
                $column,
                $isExport
            );

            $description .= <<<HTML
<div class="log-description-group">
    <span class="log-description">
        <span class="log-type">{$messageType}</span>
        {$logDescription}
    </span>
    <div class="log-date">{$createDate}</div>
</div>
HTML;
        }

        return $description;
    }
}
