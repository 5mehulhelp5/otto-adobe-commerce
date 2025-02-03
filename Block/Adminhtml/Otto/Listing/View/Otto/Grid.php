<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\View\Otto;

use M2E\Otto\Block\Adminhtml\Log\AbstractGrid;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;
use M2E\Otto\Model\Product;

class Grid extends \M2E\Otto\Block\Adminhtml\Listing\View\AbstractGrid
{
    private const STATUS_INCOMPLETE = 'Incomplete';

    private \M2E\Otto\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\Otto\Helper\Data\Session $sessionDataHelper;
    private \M2E\Otto\Model\Currency $currency;
    private ListingProductResource $listingProductResource;
    private \M2E\Otto\Helper\Url $urlHelper;
    private \M2E\Otto\Model\Magento\ProductFactory $ourMagentoProductFactory;

    public function __construct(
        ListingProductResource $listingProductResource,
        \M2E\Otto\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\Otto\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\Otto\Helper\Data\Session $sessionDataHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Otto\Helper\Data $dataHelper,
        \M2E\Otto\Helper\Url $urlHelper,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Otto\Model\Currency $currency,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->currency = $currency;
        $this->listingProductResource = $listingProductResource;
        $this->urlHelper = $urlHelper;
        $this->ourMagentoProductFactory = $ourMagentoProductFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $dataHelper,
            $globalDataHelper,
            $sessionDataHelper,
            $data
        );
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setDefaultSort(false);

        $this->setId('ottoListingViewGrid' . $this->listing->getId());

        $this->showAdvancedFilterProductsOption = false;
    }

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->order($columnIndex . ' ' . strtoupper($column->getDir()));
        }

        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->magentoProductCollectionFactory->create();
        $collection->setListingProductModeOn();
        $collection->setStoreId($this->listing->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $listingProductTableName = $this->listingProductResource->getMainTable();
        $collection->joinTable(
            ['lp' => $listingProductTableName],
            sprintf('%s = entity_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                'id' => ListingProductResource::COLUMN_ID,
                'status' => ListingProductResource::COLUMN_STATUS,
                'product_id' => ListingProductResource::COLUMN_OTTO_PRODUCT_SKU,
                'additional_data' => ListingProductResource::COLUMN_ADDITIONAL_DATA,
                'online_title' => ListingProductResource::COLUMN_ONLINE_TITLE,
                'online_qty' => ListingProductResource::COLUMN_ONLINE_QTY,
                'online_sku' => ListingProductResource::COLUMN_ONLINE_SKU,
                'online_category' => ListingProductResource::COLUMN_ONLINE_CATEGORY,
                'online_price' => ListingProductResource::COLUMN_ONLINE_PRICE,
                'template_category_id' => ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                'otto_product_url' => ListingProductResource::COLUMN_OTTO_PRODUCT_URL,
                'product_moin' => ListingProductResource::COLUMN_PRODUCT_MOIN,
                'is_incomplete' => ListingProductResource::COLUMN_IS_INCOMPLETE,
            ],
            '{{table}}.listing_id=' . $this->listing->getId()
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCsvListingGrid', __('CSV'));

        $this->addColumn('product_id', [
            'header' => __('Product ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'entity_id',
            'store_id' => $this->listing->getStoreId(),
            'renderer' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
        ]);

        $this->addColumn('name', [
            'header' => __('Product Title / Product SKU'),
            'header_export' => __('Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'online_title',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('otto_product_sku', [
            'header' => __('Otto Product SKU'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => 'product_id',
            'account_id' => $this->listing->getAccountId(),
        ]);

        $this->addColumn('product_moin', [
            'header' => __('MOIN'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => 'product_moin',
            'account_id' => $this->listing->getAccountId(),
            'renderer' => \M2E\Otto\Block\Adminhtml\Otto\Grid\Column\Renderer\OttoProductMoin::class
        ]);

        $this->addColumn(
            'online_qty',
            [
                'header' => __('Available QTY'),
                'align' => 'right',
                'width' => '50px',
                'type' => 'number',
                'index' => 'online_qty',
                'sortable' => true,
                'filter_index' => 'online_qty',
                'frame_callback' => [$this, 'callbackColumnQty'],
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'align' => 'right',
                'width' => '50px',
                'type' => 'number',
                'rate' => 1,
                'index' => 'online_price',
                'filter_index' => 'online_price',
                'frame_callback' => [$this, 'callbackColumnPrice'],
            ]
        );

        $statusColumn = [
            'header' => __('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'sortable' => false,
            'options' => [
                Product::STATUS_NOT_LISTED => Product::getStatusTitle(Product::STATUS_NOT_LISTED),
                Product::STATUS_LISTED => Product::getStatusTitle(Product::STATUS_LISTED),
                Product::STATUS_INACTIVE => Product::getStatusTitle(Product::STATUS_INACTIVE),
                self::STATUS_INCOMPLETE => Product::getIncompleteStatusTitle(),
            ],
            'showLogIcon' => true,
            'renderer' => \M2E\Otto\Block\Adminhtml\Otto\Grid\Column\Renderer\Status::class,
            'filter_condition_callback' => [$this, 'callbackFilterStatus'],
        ];

        $this->addColumn('status', $statusColumn);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Configure groups
        // ---------------------------------------

        $groups = [
            'actions' => __('Listing Actions'),
            'other' => __('Other'),
        ];

        $this->getMassactionBlock()->setGroups($groups);

        // Set mass-action
        // ---------------------------------------

        $this->getMassactionBlock()->addItem('list', [
            'label' => __('List Item(s) on Otto'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('revise', [
            'label' => __('Revise Item(s) on Otto'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('relist', [
            'label' => __('Relist Item(s) on Otto'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('stop', [
            'label' => __('Stop Item(s) on Otto'),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('stopAndRemove', [
            'label' => __('Stop on Otto / Remove From Listing'),
            'url' => '',
        ], 'actions');

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    public function callbackColumnQty($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return (string)$value;
        }

        if ((int)$row['status'] === \M2E\Otto\Model\Product::STATUS_INACTIVE) {
            return sprintf(
                '<span style="color: gray; text-decoration: line-through;">%s</span>',
                $value
            );
        }

        if ((int)$row['status'] === \M2E\Otto\Model\Product::STATUS_NOT_LISTED) {
            return sprintf(
                '<span style="color: gray">%s</span>',
                __('Not Listed')
            );
        }

        if ($value <= 0) {
            return 0;
        }

        return (int)$value;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = $row->getName();

        $onlineTitle = $row->getData('online_title');
        if (!empty($onlineTitle)) {
            $title = $onlineTitle;
        }

        $title = \M2E\Otto\Helper\Data::escapeHtml($title);

        $valueHtml = '<span class="product-title-value">' . $title . '</span>';

        $sku = $row->getData('sku');

        if ($row->getData('sku') === null) {
            $sku = $this->ourMagentoProductFactory->create()
                                                  ->setProductId($row->getData('entity_id'))
                                                  ->getSku();
        }

        if ($isExport) {
            return \M2E\Otto\Helper\Data::escapeHtml($sku);
        }

        $valueHtml .= '<br/>' .
            '<strong>' . __('SKU') . ':</strong>&nbsp;' .
            \M2E\Otto\Helper\Data::escapeHtml($sku);

        if ($categoryId = $row->getData('online_category')) {
            $categoryPath = $row->getData('category_path');
            $categoryInfo = sprintf('%s %s', $categoryPath, $categoryId);
            $valueHtml .= '<br/><br/>' .
                '<strong>' . __('Category') . ':</strong>&nbsp;' .
                \M2E\Otto\Helper\Data::escapeHtml($categoryInfo);
        }

        return $valueHtml;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute' => 'sku', 'like' => '%' . $value . '%'],
                ['attribute' => 'name', 'like' => '%' . $value . '%'],
                ['attribute' => 'online_title', 'like' => '%' . $value . '%'],
                ['attribute' => 'online_category', 'like' => '%' . $value . '%'],
            ]
        );
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return (string)$value;
        }

        $productStatus = $row->getData('status');

        if ((int)$productStatus === \M2E\Otto\Model\Product::STATUS_NOT_LISTED) {
            return sprintf(
                '<span style="color: gray;">%s</span>',
                __('Not Listed')
            );
        }

        return $this->currency->formatPrice(
            'EUR',
            (float)$value
        );
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

        if (is_array($value) && isset($value['value'])) {
            $collection->addFieldToFilter($index, (int)$value['value']);
        } else {
            $collection->addFieldToFilter($index, (int)$value);
        }

        $collection->addFieldToFilter(ListingProductResource::COLUMN_IS_INCOMPLETE, 0);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/otto_listing/view', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    public function getTooltipHtml($content, $id = '', $customClasses = []): string
    {
        return <<<HTML
<div id="{$id}" class="Otto-field-tooltip admin__field-tooltip">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content" style="">
        {$content}
    </div>
</div>
HTML;
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
                <<<JS
                OttoListingViewOttoGridObj.afterInitPage();
JS
            );

            return parent::_toHtml();
        }

        $temp = $this->sessionDataHelper->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = 'OttoListingViewGrid' . $this->listing['id'];
        $ignoreListings = \M2E\Otto\Helper\Json::encode([$this->listing['id']]);

        $this->jsUrl->addUrls([
            'runListProducts' => $this->getUrl('*/otto_listing/runListProducts'),
            'runRelistProducts' => $this->getUrl('*/otto_listing/runRelistProducts'),
            'runReviseProducts' => $this->getUrl('*/otto_listing/runReviseProducts'),
            'runStopProducts' => $this->getUrl('*/otto_listing/runStopProducts'),
            'runStopAndRemoveProducts' => $this->getUrl('*/otto_listing/runStopAndRemoveProducts'),
            'previewItems' => $this->getUrl('*/otto_listing/previewItems'),
        ]);

        $this->jsUrl->add(
            $this->getUrl('*/otto_listing/saveCategoryTemplate', [
                'listing_id' => $this->listing['id'],
            ]),
            'otto_listing/saveCategoryTemplate'
        );

        $this->jsUrl->add(
            $this->getUrl('*/otto_log_listing_product/index'),
            'otto_log_listing_product/index'
        );

        $this->jsUrl->add(
            $this->getUrl('*/otto_log_listing_product/index', [
                AbstractGrid::LISTING_ID_FIELD =>
                    $this->listing['id'],
                'back' => $this->urlHelper->makeBackUrlParam(
                    '*/otto_listing/view',
                    ['id' => $this->listing['id']]
                ),
            ]),
            'logViewUrl'
        );
        $this->jsUrl->add($this->getUrl('*/listing/getErrorsSummary'), 'getErrorsSummary');

        $this->jsUrl->add(
            $this->getUrl('*/otto_listing_moving/moveToListingGrid'),
            'otto_listing_moving/moveToListingGrid'
        );

        $this->jsUrl->add(
            $this->getUrl('*/otto_listing/getListingProductBids'),
            'otto_listing/getListingProductBids'
        );

        $this->jsTranslator->addTranslations([
            'task_completed_message' => __('Task completed. Please wait ...'),

            'task_completed_warning_message' => __('"%task_title%" task has completed with warnings. <a target="_blank" href="%url%">View Log</a> for details.'),
            'task_completed_error_message' => __('"%task_title%" task has completed with errors. <a target="_blank" href="%url%">View Log</a> for details.'),

            'sending_data_message' => __('Sending %product_title% Product(s) data on Otto.'),

            'View Full Product Log' => __('View Full Product Log.'),

            'The Listing was locked by another process. Please try again later.' =>
                __('The Listing was locked by another process. Please try again later.'),

            'Listing is empty.' => __('Listing is empty.'),

            'listing_all_items_message' => __('Listing All Items On Otto'),
            'listing_selected_items_message' => __('Listing Selected Items On Otto'),
            'revising_selected_items_message' => __('Revising Selected Items On Otto'),
            'relisting_selected_items_message' => __('Relisting Selected Items On Otto'),
            'stopping_selected_items_message' => __('Stopping Selected Items On Otto'),
            'stopping_and_removing_selected_items_message' => __(
                'Stopping On Otto And Removing From Listing Selected Items'
            ),
            'removing_selected_items_message' => __('Removing From Listing Selected Items'),

            'Please select the Products you want to perform the Action on.' =>
                __('Please select the Products you want to perform the Action on.'),

            'Please select Action.' => __('Please select Action.'),

            'Specifics' => __('Specifics'),
        ]);

        $this->js->add(
            <<<JS
    Otto.productsIdsForList = '{$productsIdsForList}';
    Otto.customData.gridId = '{$gridId}';
    Otto.customData.ignoreListings = '{$ignoreListings}';
JS
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'Otto/Otto/Listing/View/Otto/Grid',
        'Otto/Otto/Listing/VariationProductManage'
    ], function(){

        window.OttoListingViewOttoGridObj = new OttoListingViewOttoGrid(
            '{$this->getId()}',
            {$this->listing['id']}
        );
        OttoListingViewOttoGridObj.afterInitPage();

        OttoListingViewOttoGridObj.actionHandler.setProgressBar('listing_view_progress_bar');
        OttoListingViewOttoGridObj.actionHandler.setGridWrapper('listing_view_content_container');

        if (Otto.productsIdsForList) {
            OttoListingViewOttoGridObj.getGridMassActionObj().checkedString = Otto.productsIdsForList;
            OttoListingViewOttoGridObj.actionHandler.listAction();
        }
    });
JS
        );

        return parent::_toHtml();
    }
}
