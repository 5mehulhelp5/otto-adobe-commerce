<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\Unmanaged;

use M2E\Otto\Model\Product;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;

class Grid extends \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private const STATUS_INCOMPLETE = 'Incomplete';

    protected \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\Otto\Model\Listing\Other\Repository $unmanagedRepository;
    private \M2E\Otto\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage;

    public function __construct(
        \M2E\Otto\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage,
        \M2E\Otto\Model\Listing\Other\Repository $unmanagedRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->localeCurrency = $localeCurrency;
        $this->resourceConnection = $resourceConnection;
        $this->unmanagedRepository = $unmanagedRepository;
        $this->uiAccountRuntimeStorage = $uiAccountRuntimeStorage;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ottoListingUnmanagedGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/otto_listing_unmanaged/index',
            ['_current' => true, 'account' => $this->uiAccountRuntimeStorage->getAccount()->getId()]
        );
    }

    protected function _prepareCollection()
    {
        $collection = $this->unmanagedRepository->createCollection();

        $collection->addFieldToFilter('account_id', $this->uiAccountRuntimeStorage->getAccount()->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return \M2E\Otto\Block\Adminhtml\Otto\Listing\Unmanaged\Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCsvUnmanagedGrid', __('CSV'));

        $this->addColumn('magento_product_id', [
            'header' => __('Product ID'),
            'align' => 'left',
            'type' => 'number',
            'width' => '80px',
            'index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID,
            'filter_index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID,
            'frame_callback' => [$this, 'callbackColumnProductId'],
            'filter' => \M2E\Otto\Block\Adminhtml\Grid\Column\Filter\ProductId::class,
            'filter_condition_callback' => [$this, 'callbackFilterProductId'],
        ]);

        $this->addColumn('title', [
            'header' => __(
                'Product Title / Product SKU / %channel_title Category',
                [
                    'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()
                ]
            ),
            'header_export' => __('Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_TITLE,
            'escape' => false,
            'filter_index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_TITLE,
            'frame_callback' => [$this, 'callbackColumnProductTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('sku', [
            'header' => __('SKU'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_SKU,
            'filter_index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_SKU,
        ]);

        $this->addColumn('moin', [
            'header' => __('MOIN'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MOIN,
            'frame_callback' => [$this, 'callbackColumnMoin'],
            'filter_index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MOIN,
        ]);

        $this->addColumn('online_qty', [
            'header' => __('Available QTY'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_QTY,
            'frame_callback' => [$this, 'callbackColumnQty']
        ]);

        $this->addColumn('online_price', [
            'header' => __('Price'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_PRICE,
            'filter_index' => \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_PRICE,
            'frame_callback' => [$this, 'callbackColumnOnlinePrice'],
        ]);

        $this->addColumn('status', [
            'header' => __('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'sortable' => false,
            'options' => [
                Product::STATUS_LISTED => Product::getStatusTitle(Product::STATUS_LISTED),
                Product::STATUS_INACTIVE => Product::getStatusTitle(Product::STATUS_INACTIVE),
                self::STATUS_INCOMPLETE => Product::getIncompleteStatusTitle(),
            ],
            'frame_callback' => [$this, 'callbackColumnStatus'],
            'filter_condition_callback' => [$this, 'callbackFilterStatus'],
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set mass-action identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->setGroups([
            'mapping' => __('Linking'),
            'other' => __('Other'),
        ]);

        $this->getMassactionBlock()->addItem('autoMapping', [
            'label' => __('Link Item(s) Automatically'),
            'url' => '',
        ], 'mapping');

        $this->getMassactionBlock()->addItem('moving', [
            'label' => __('Move Item(s) to Listing'),
            'url' => '',
        ], 'other');
        $this->getMassactionBlock()->addItem('removing', [
            'label' => __('Remove Item(s)'),
            'url' => '',
        ], 'other');
        $this->getMassactionBlock()->addItem('unmapping', [
            'label' => __('Unlink Item(s)'),
            'url' => '',
        ], 'mapping');

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/other/view/grid.css');

        return parent::_prepareLayout();
    }

    /**
     * @param $value
     * @param \M2E\Otto\Model\Listing\Other $row
     * @param $column
     * @param $isExport
     *
     * @return string
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            if ($isExport) {
                return '';
            }

            $productTitle = $row->getTitle();
            if (strlen($productTitle) > 60) {
                $productTitle = substr($productTitle, 0, 60) . '...';
            }
            $productTitle = \M2E\Core\Helper\Data::escapeHtml($productTitle);
            $productTitle = \M2E\Core\Helper\Data::escapeJs($productTitle);

            return sprintf(
                '<a onclick="ListingOtherMappingObj.openPopUp(%s, \'%s\')">%s</a>',
                (int)$row->getId(),
                $productTitle,
                __('Link')
            );
        }

        if ($isExport) {
            return $row->getMagentoProductId();
        }

        $viewProductUrl = $this->getUrl(
            'catalog/product/edit',
            ['id' => $row->getMagentoProductId()]
        );

        $editLink = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $viewProductUrl,
            $row->getMagentoProductId()
        );

        $moveLink = sprintf(
            '<a onclick="OttoListingOtherGridObj.movingHandler.getGridHtml(%s)">%s</a>',
            \M2E\Core\Helper\Json::encode([(int)$row->getId()]),
            __('Move')
        );

        return $editLink . ' &nbsp;&nbsp;&nbsp;' . $moveLink;
    }

    /**
     * @param $value
     * @param \M2E\Otto\Model\Listing\Other $row
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        $title = $row->getTitle();

        $titleSku = __('SKU');

        $tempSku = $row->getSku();
        $tempSku = \M2E\Core\Helper\Data::escapeHtml($tempSku);

        if ($isExport) {
            return strip_tags($tempSku);
        }

        $categoryHtml = sprintf(
            '<strong>%s:</strong>&nbsp;%s',
            __('Category'),
            \M2E\Core\Helper\Data::escapeHtml($row->getCategory())
        );

        return sprintf(
            '<span>%s</span><br/><strong>%s:&nbsp;</strong>%s<br/>%s',
            \M2E\Core\Helper\Data::escapeHtml($title),
            $titleSku,
            $tempSku,
            $categoryHtml
        );
    }

    /**
     * @param $value
     * @param \M2E\Otto\Model\Listing\Other $row
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnMoin($value, $row, $column, $isExport)
    {
        $moin = $row->getOttoProductMoin();

        if ($moin === null || $moin === '') {
            if ($isExport) {
                return '';
            }

            return __('N/A');
        }

        if ($isExport) {
            return $moin;
        }

        if ($row->getOttoProductUrl() === null) {
            return $moin;
        }

        return sprintf('<a href="%s" target="_blank">%s</a>', $row->getOttoProductUrl(), $moin);
    }

    public function callbackColumnQty($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return (string)$value;
        }

        if ((int)$row['status'] === \M2E\Otto\Model\Product::STATUS_INACTIVE) {
            return sprintf(
                '<span style="color: gray">%s</span>',
                __('N/A')
            );
        }

        if ($value <= 0) {
            return 0;
        }

        return (int)$value;
    }

    /**
     * @param $value
     * @param \M2E\Otto\Model\Listing\Other $row
     * @param $column
     * @param $isExport
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function callbackColumnItemId($value, $row, $column, $isExport)
    {
        $value = $row->getSku();

        if ($isExport) {
            return $value;
        }

        if (empty($value)) {
            return __('N/A');
        }

        return $value;
    }

    /**
     * @param $value
     * @param \M2E\Otto\Model\Listing\Other $row
     * @param $column
     * @param $isExport
     *
     * @return int|\Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Currency\Exception\CurrencyException
     */
    public function callbackColumnOnlinePrice($value, $row, $column, $isExport)
    {
        $value = $row->getPrice();
        if (empty($value)) {
            if ($isExport) {
                return '';
            }

            return __('N/A');
        }

        if ($value <= 0) {
            if ($isExport) {
                return 0;
            }

            return '<span style="color: #f00;">0</span>';
        }

        return $this->localeCurrency
            ->getCurrency($row->getCurrency())
            ->toCurrency($value);
    }

    /**
     * @param $value
     * @param \M2E\Otto\Model\Listing\Other $row
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return $value;
        }

        $coloredStatuses = [
            \M2E\Otto\Model\Product::STATUS_LISTED => 'green',
            \M2E\Otto\Model\Product::STATUS_INACTIVE => 'red',
            self::STATUS_INCOMPLETE => 'orange',

        ];

        $status = $row->getStatus();

        if ($row->isProductIncomplete()) {
            $status = $value = self::STATUS_INCOMPLETE;
        }

        if (isset($coloredStatuses[$status])) {
            $value = sprintf(
                '<span style="color: %s">%s</span>',
                $coloredStatuses[$status],
                $value
            );
        }

        return $value . $this->getLockedTag($row);
    }

    protected function callbackFilterStatus($collection, $column): void
    {
        $value = $column->getFilter()->getValue();
        $index = $column->getIndex();

        if ($value === null) {
            return;
        }

        if ($value === self::STATUS_INCOMPLETE) {
            $collection->addFieldToFilter(ListingProductResource::COLUMN_IS_INCOMPLETE, 1);
            return;
        }

        $fieldValue = is_array($value) ? (int)$value['value'] : (int)$value;

        if ($fieldValue) {
            $collection->addFieldToFilter($index, $fieldValue);
            $collection->addFieldToFilter(ListingProductResource::COLUMN_IS_INCOMPLETE, 0);
        }
    }

    protected function callbackFilterProductId($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= sprintf(
                '%s >= %s',
                \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID,
                $collection->getConnection()->quote($value['from'])
            );
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= sprintf(
                '%s <= %s',
                \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID,
                $collection->getConnection()->quote($value['to'])
            );
        }

        if (isset($value['is_mapped']) && $value['is_mapped'] !== '') {
            if (!empty($where)) {
                $where = '(' . $where . ') AND ';
            }

            if ($value['is_mapped']) {
                $where .= sprintf(
                    '%s IS NOT NULL',
                    \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID
                );
            } else {
                $where .= sprintf(
                    '%s IS NULL',
                    \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID
                );
            }
        }

        $collection->getSelect()->where($where);
    }

    /**
     * @param \M2E\Otto\Model\ResourceModel\Listing\Other\Collection $collection
     * @param $column
     */
    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $condition = sprintf(
            '%s LIKE ? OR %s LIKE ? OR %s LIKE ?',
            \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_TITLE,
            \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_SKU,
            \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_CATEGORY
        );
        $collection->getSelect()->where($condition, "%$value%");
    }

    private function getLockedTag(\M2E\Otto\Model\Listing\Other $listingOther): string
    {
        $html = '';
        $processingLocks = [];
        foreach ($processingLocks as $processingLock) {
            switch ($processingLock->getTag()) {
                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                default:
                    break;
            }
        }

        return $html;
    }

    protected function _beforeToHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest() || $this->getRequest()->getParam('isAjax')) {
            $this->js->addRequireJs(
                [
                    'jQuery' => 'jquery',
                ],
                <<<JS

            OttoListingOtherGridObj.afterInitPage();
JS
            );
        }

        return parent::_beforeToHtml();
    }

    public function getRowUrl($item)
    {
        return false;
    }
}
