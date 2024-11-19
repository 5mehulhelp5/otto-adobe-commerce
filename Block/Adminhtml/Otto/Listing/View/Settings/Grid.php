<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\View\Settings;

use M2E\Otto\Block\Adminhtml\Otto\Listing\View\Settings\Grid\Column\Filter\PolicySettings
    as PolicySettingsFilter;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;
use M2E\Otto\Model\ResourceModel\Template\Description as DescriptionResource;
use M2E\Otto\Model\ResourceModel\Template\SellingFormat as SellingFormatResource;
use M2E\Otto\Model\ResourceModel\Template\Synchronization as SynchronizationResource;
use M2E\Otto\Model\ResourceModel\Category as CategoryResource;
use M2E\Otto\Model\Otto\Template\Manager;

class Grid extends \M2E\Otto\Block\Adminhtml\Listing\View\AbstractGrid
{
    private CategoryResource $categoryResource;
    private \M2E\Otto\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\Otto\Helper\Data\Session $sessionDataHelper;
    private ListingProductResource $listingProductResource;
    private \M2E\Otto\Helper\Url $urlHelper;
    private \M2E\Otto\Model\Magento\ProductFactory $magentoProductFactory;
    private DescriptionResource $descriptionResource;
    private SellingFormatResource $sellingFormatResource;
    private SynchronizationResource $synchronizationResource;
    private \M2E\Otto\Model\Template\Description\Repository $descriptionRepository;
    private \M2E\Otto\Model\Template\SellingFormat\Repository $sellingFormatRepository;
    private \M2E\Otto\Model\Template\Synchronization\Repository $synchronizationRepository;

    public function __construct(
        CategoryResource $categoryResource,
        \M2E\Otto\Model\Template\Description\Repository $descriptionRepository,
        \M2E\Otto\Model\Template\SellingFormat\Repository $sellingFormatRepository,
        \M2E\Otto\Model\Template\Synchronization\Repository $synchronizationRepository,
        DescriptionResource $descriptionResource,
        SellingFormatResource $sellingFormatResource,
        SynchronizationResource $synchronizationResource,
        \M2E\Otto\Model\Magento\ProductFactory $magentoProductFactory,
        ListingProductResource $listingProductResource,
        \M2E\Otto\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Otto\Helper\Data\Session $sessionDataHelper,
        \M2E\Otto\Helper\Data $dataHelper,
        \M2E\Otto\Helper\Url $urlHelper,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->categoryResource = $categoryResource;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->urlHelper = $urlHelper;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->listingProductResource = $listingProductResource;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->descriptionResource = $descriptionResource;
        $this->sellingFormatResource = $sellingFormatResource;
        $this->synchronizationResource = $synchronizationResource;
        $this->descriptionRepository = $descriptionRepository;
        $this->sellingFormatRepository = $sellingFormatRepository;
        $this->synchronizationRepository = $synchronizationRepository;
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

        $this->setId('ottoListingViewGrid' . $this->listing->getId());

        $this->css->addFile('otto/template.css');
        $this->css->addFile('listing/grid.css');

        $this->hideMassactionColumn = false;
        $this->hideMassactionDropDown = false;
        $this->showAdvancedFilterProductsOption = false;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection(): Grid
    {
        $collection = $this->magentoProductCollectionFactory->create();

        $collection->setListingProductModeOn();
        $collection->setStoreId($this->listing->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $lpTable = $this->listingProductResource->getMainTable();
        $collection->joinTable(
            ['lp' => $lpTable],
            sprintf('%s = entity_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                'id' => ListingProductResource::COLUMN_ID,
                'status' => ListingProductResource::COLUMN_STATUS,
                'additional_data' => ListingProductResource::COLUMN_ADDITIONAL_DATA,
                'online_title' => ListingProductResource::COLUMN_ONLINE_TITLE,
                'available_qty' => ListingProductResource::COLUMN_ONLINE_QTY,
                'online_category' => ListingProductResource::COLUMN_ONLINE_CATEGORY,
                'online_current_price' => ListingProductResource::COLUMN_ONLINE_PRICE,
                'online_brand_name' => ListingProductResource::COLUMN_ONLINE_BRAND_NAME,
                'online_brand_id' => ListingProductResource::COLUMN_ONLINE_BRAND_ID,
                'template_category_id' => ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                'template_description_mode' => ListingProductResource::COLUMN_TEMPLATE_DESCRIPTION_MODE,
                'template_selling_format_mode' => ListingProductResource::COLUMN_TEMPLATE_SELLING_FORMAT_MODE,
                'template_synchronization_mode' => ListingProductResource::COLUMN_TEMPLATE_SYNCHRONIZATION_MODE,
                'template_description_id' => ListingProductResource::COLUMN_TEMPLATE_DESCRIPTION_ID,
                'template_selling_format_id' => ListingProductResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID,
                'template_synchronization_id' => ListingProductResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
                'template_shipping_id' => ListingProductResource::COLUMN_TEMPLATE_SHIPPING_ID,
            ],
            sprintf(
                '{{table}}.%s = %s',
                ListingProductResource::COLUMN_LISTING_ID,
                $this->listing->getId()
            )
        );

        $templateDescriptionTableName = $this->descriptionResource->getMainTable();
        $templateSellingFormatTableName = $this->sellingFormatResource->getMainTable();
        $templateSynchronizationTableName = $this->synchronizationResource->getMainTable();
        $categoryTableName = $this->categoryResource->getMainTable();
        $collection
            ->joinTable(
                ['td' => $templateDescriptionTableName],
                sprintf('%s = template_description_id', DescriptionResource::COLUMN_ID),
                [
                    'description_policy_title' => DescriptionResource::COLUMN_TITLE,
                ],
                null,
                'left'
            )
            ->joinTable(
                ['tsf' => $templateSellingFormatTableName],
                sprintf('%s = template_selling_format_id', SellingFormatResource::COLUMN_ID),
                ['selling_policy_title' => SellingFormatResource::COLUMN_TITLE],
                null,
                'left'
            )
            ->joinTable(
                ['ts' => $templateSynchronizationTableName],
                sprintf('%s = template_synchronization_id', SynchronizationResource::COLUMN_ID),
                ['synchronization_policy_title' => SynchronizationResource::COLUMN_TITLE],
                null,
                'left'
            )
            ->joinTable(
                ['category' => $categoryTableName],
                sprintf('%s = template_category_id', CategoryResource::COLUMN_ID),
                [
                    'category_title' => CategoryResource::COLUMN_TITLE,
                    'category_id' => CategoryResource::COLUMN_ID,
                ],
                null,
                'left'
            );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @throws \Exception
     */
    protected function _prepareColumns(): Grid
    {
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product ID'),
                'align' => 'right',
                'width' => '100px',
                'type' => 'number',
                'index' => 'entity_id',
                'store_id' => $this->listing->getStoreId(),
                'renderer' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Product Title / Product SKU'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'name',
                'escape' => false,
                'frame_callback' => [$this, 'callbackColumnTitle'],
                'filter_condition_callback' => [$this, 'callbackFilterTitle'],
            ]
        );

        $this->addColumn(
            'category',
            [
                'header' => __('Otto Category'),
                'align' => 'left',
                'width' => '200px',
                'type' => 'text',
                'frame_callback' => [$this, 'callbackColumnCategory'],
                'filter_condition_callback' => [$this, 'callbackFilterCategory'],
            ]
        );

        $this->addColumn(
            'setting',
            [
                'index' => 'name',
                'header' => __('Listing Policies Overrides'),
                'align' => 'left',
                'type' => 'text',
                'sortable' => false,
                'filter' => PolicySettingsFilter::class,
                'frame_callback' => [$this, 'callbackColumnSetting'],
                'filter_condition_callback' => [$this, 'callbackFilterSetting'],
                'column_css_class' => 'listing-grid-column-setting',
            ]
        );

        $this->addColumn('actions', [
            'header' => $this->__('Actions'),
            'align' => 'left',
            'type' => 'action',
            'index' => 'actions',
            'filter' => false,
            'sortable' => false,
            'renderer' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'field' => 'id',
            'group_order' => $this->getGroupOrder(),
            'actions' => $this->getColumnActionsItems(),
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $this->getMassactionBlock()->setGroups([
            'edit_categories_settings' => $this->__('Edit Category Settings'),
            'other' => $this->__('Other'),
        ]);

        $this->getMassactionBlock()->addItem('editCategorySettings', [
            'label' => $this->__('Categories & Attributes'),
            'url' => '',
        ], 'edit_categories_settings');

        $this->getMassactionBlock()->addItem('moving', [
            'label' => $this->__('Move Item(s) to Another Listing'),
            'url' => '',
        ], 'other');

        return $this;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport): string
    {
        $value = '<span>' . \M2E\Otto\Helper\Data::escapeHtml($value) . '</span>';

        $sku = $row->getData('sku');
        if ($sku === null) {
            $sku = $this->magentoProductFactory
                ->create()
                ->setProductId($row->getData('entity_id'))
                ->getSku();
        }

        $value .= '<br/><strong>' . __('SKU') . ':</strong>&nbsp;';
        $value .= \M2E\Otto\Helper\Data::escapeHtml($sku);

        return $value;
    }

    public function callbackColumnCategory($value, $row, $column, $isExport): string
    {
        $categoryTitle = $row->getData('category_title');

        return <<<HTML
    <div>
        <p style="padding: 2px 0 0 10px">{$categoryTitle}</p>
    </div>
HTML;
    }

    public function callbackColumnSetting($value, $row, $column, $isExport): string
    {
        $templatesNames = [
            Manager::TEMPLATE_SELLING_FORMAT => __('Selling'),
            Manager::TEMPLATE_DESCRIPTION => __('Description'),
            Manager::TEMPLATE_SYNCHRONIZATION => __('Synchronization'),
        ];

        $modes = array_keys($templatesNames);
        $listingSettings = array_filter($modes, function ($templateNick) use ($row) {
            $templateMode = $row->getData('template_' . $templateNick . '_mode');

            return $templateMode == Manager::MODE_PARENT;
        });

        if (count($listingSettings) === count($templatesNames)) {
            return __('Use from Listing Settings');
        }

        $html = '';
        foreach ($templatesNames as $templateNick => $templateTitle) {
            $templateMode = $row->getData('template_' . $templateNick . '_mode');

            if ($templateMode == Manager::MODE_PARENT) {
                continue;
            }

            $templateLink = '';
            if ($templateMode == Manager::MODE_CUSTOM) {
                $templateLink = '<span>' . __('Custom Settings') . '</span>';
            } elseif ($templateMode == Manager::MODE_TEMPLATE) {
                $id = (int)$row->getData('template_' . $templateNick . '_id');

                $url = $this->getUrl('*/otto_template/edit', [
                    'id' => $id,
                    'nick' => $templateNick,
                ]);

                $objTitle = '';
                if ($templateNick === Manager::TEMPLATE_SELLING_FORMAT) {
                    $objTitle = $this->sellingFormatRepository->get($id)->getTitle();
                } elseif ($templateNick === Manager::TEMPLATE_DESCRIPTION) {
                    $objTitle = $this->descriptionRepository->get($id)->getTitle();
                } else {
                    $objTitle = $this->synchronizationRepository->get($id)->getTitle();
                }

                $templateLink = '<a href="' . $url . '" target="_blank">' . $objTitle . '</a>';
            }

            $html .= "<div style='padding: 2px 0 0 0px'>
                                    <strong>$templateTitle:</strong>
                                    <span style='padding: 0 0px 0 5px'>$templateLink</span>
                               </div>";
        }

        return $html;
    }

    public function callbackFilterTitle($collection, $column)
    {
        $inputValue = $column->getFilter()->getValue();

        if ($inputValue !== null) {
            $fieldsToFilter = [
                ['attribute' => 'sku', 'like' => '%' . $inputValue . '%'],
                ['attribute' => 'name', 'like' => '%' . $inputValue . '%'],
            ];

            $collection->addFieldToFilter($fieldsToFilter);
        }
    }

    public function callbackFilterCategory($collection, $column)
    {
        $filter = $column->getFilter();
        if ($value = $filter->getValue()) {
            $collection->getSelect()->where('online_category LIKE ?', '%' . $value . '%');
        }
    }

    public function callbackFilterSetting($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $inputValue = null;

        if (is_array($value) && isset($value['input'])) {
            $inputValue = $value['input'];
        } elseif (is_string($value)) {
            $inputValue = $value;
        }

        if ($inputValue !== null) {
            /** @var \M2E\Otto\Model\ResourceModel\Magento\Product\Collection $collection */
            $collection->addAttributeToFilter(
                [
                    ['attribute' => 'description_policy_title', 'like' => '%' . $inputValue . '%'],
                    ['attribute' => 'selling_policy_title', 'like' => '%' . $inputValue . '%'],
                    ['attribute' => 'synchronization_policy_title', 'like' => '%' . $inputValue . '%'],
                ]
            );
        }

        if (isset($value['select'])) {
            switch ($value['select']) {
                case Manager::MODE_PARENT:
                    // no policy overrides

                    $collection->addAttributeToFilter(
                        'template_description_mode',
                        ['eq' => Manager::MODE_PARENT]
                    );
                    $collection->addAttributeToFilter(
                        'template_selling_format_mode',
                        ['eq' => Manager::MODE_PARENT]
                    );
                    $collection->addAttributeToFilter(
                        'template_synchronization_mode',
                        ['eq' => Manager::MODE_PARENT]
                    );
                    break;
                case Manager::MODE_TEMPLATE:
                case Manager::MODE_CUSTOM:
                    // policy templates and custom settings
                    $collection->addAttributeToFilter(
                        [

                            [
                                'attribute' => 'template_description_mode',
                                'eq' => (int)$value['select'],
                            ],
                            [
                                'attribute' => 'template_selling_format_mode',
                                'eq' => (int)$value['select'],
                            ],
                            [
                                'attribute' => 'template_synchronization_mode',
                                'eq' => (int)$value['select'],
                            ],
                        ]
                    );
                    break;
            }
        }
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('*/otto_listing/view', ['_current' => true]);
    }

    public function getRowUrl($item): bool
    {
        return false;
    }

    protected function getGroupOrder(): array
    {
        return [
            'edit_categories_settings' => $this->__('Edit Category Settings'),
        ];
    }

    protected function getColumnActionsItems(): array
    {
        $actions = [
            'editCategories' => [
                'caption' => $this->__('Categories & Attributes'),
                'group' => 'edit_categories_settings',
                'field' => 'id',
                'onclick_action' => "OttoListingViewSettingsGridObj.actions['editCategorySettingsAction']",
            ],
        ];

        return $actions;
    }

    protected function _toHtml(): string
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
                <<<JS
            OttoListingViewSettingsGridObj.afterInitPage();
JS
            );

            return parent::_toHtml();
        }

        $helper = $this->dataHelper;

        // ---------------------------------------
        $this->jsPhp->addConstants(
            \M2E\Otto\Helper\Data::getClassConstants(\M2E\Otto\Model\Otto\Template\Manager::class)
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->jsUrl->addUrls($helper->getControllerActions('Otto\Listing', ['_current' => true]));

        $this->jsUrl->add(
            $this->getUrl('*/otto_log_listing_product/index', [
                \M2E\Otto\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD =>
                    $this->listing->getId(),
            ]),
            'otto_log_listing_product/index'
        );
        $this->jsUrl->add(
            $this->getUrl('*/otto_log_listing_product/index', [
                \M2E\Otto\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD =>
                    $this->listing->getId(),
                'back' => $this->urlHelper->makeBackUrlParam(
                    '*/otto_listing/view',
                    ['id' => $this->listing->getId()]
                ),
            ]),
            'logViewUrl'
        );

        $this->jsUrl->add($this->getUrl('*/listing/getErrorsSummary'), 'getErrorsSummary');

        $this->jsUrl->add($this->getUrl('*/listing_moving/moveToListingGrid'), 'moveToListingGridHtml');
        $this->jsUrl->add($this->getUrl('*/listing_moving/prepareMoveToListing'), 'prepareData');
        $this->jsUrl->add($this->getUrl('*/listing_moving/moveToListing'), 'moveToListing');
        $this->jsUrl->add(
            $this->getUrl('*/listing_product_category_settings/edit', ['_current' => true]),
            'listing_product_category_settings/edit'
        );

        $this->jsUrl->add(
            $this->getUrl('*/otto_template/editListingProductsPolicy'),
            'otto_template/editListingProductsPolicy'
        );
        $this->jsUrl->add(
            $this->getUrl('*/otto_template/saveListingProductsPolicy'),
            'otto_template/saveListingProductsPolicy'
        );

        //------------------------------
        $this->jsTranslator->addTranslations([
            'Edit Description Policy Setting' => __('Edit Description Policy Setting'),
            'Edit Selling Policy Setting' => __('Edit Selling Policy Setting'),
            'Edit Synchronization Policy Setting' => __('Edit Synchronization Policy Setting'),
            'Edit Settings' => __('Edit Settings'),
            'For' => __('For'),
            'Category Settings' => __('Category Settings'),
            'Specifics' => __('Specifics'),
            'task_completed_message' => __('Task completed. Please wait ...'),
            'sending_data_message' => __('Sending %product_title% Product(s) data on Otto.'),
            'View Full Product Log.' => __('View Full Product Log.'),
            'The Listing was locked by another process. Please try again later.' =>
                __('The Listing was locked by another process. Please try again later.'),
            'Listing is empty.' => __('Listing is empty.'),
            'Please select Items.' => __('Please select Items.'),
            'Please select Action.' => __('Please select Action.'),
            'popup_title' => __('Moving Otto Items'),
            'task_completed_warning_message' => __(
                '"%task_title%" Task has completed with warnings. <a target="_blank" href="%url%">View Log</a> for details.'
            ),
            'task_completed_error_message' => __(
                '"%task_title%" Task has completed with errors. <a target="_blank" href="%url%">View Log</a> for details.'
            ),
            'Add New Listing' => __('Add New Listing'),
        ]);

        $temp = $this->sessionDataHelper->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $ignoreListings = \M2E\Otto\Helper\Json::encode([$this->listing->getId()]);

        $this->js->add(
            <<<JS
    Otto.productsIdsForList = '{$productsIdsForList}';
    Otto.customData.gridId = '{$this->getId()}';
    Otto.customData.ignoreListings = '{$ignoreListings}';
JS
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'Otto/Otto/Listing/View/Settings/Grid'
    ], function(){

        window.OttoListingViewSettingsGridObj = new OttoListingViewSettingsGrid(
            '{$this->getId()}',
            '{$this->listing->getId()}',
            '{$this->listing->getAccountId()}'
        );
        OttoListingViewSettingsGridObj.afterInitPage();
        OttoListingViewSettingsGridObj.movingHandler.setProgressBar('listing_view_progress_bar');
        OttoListingViewSettingsGridObj.movingHandler.setGridWrapper('listing_view_content_container');
    });
JS
        );

        return parent::_toHtml();
    }
}
