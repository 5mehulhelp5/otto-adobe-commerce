<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\ItemsByListing;

use M2E\Otto\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite;

class Grid extends \M2E\Otto\Block\Adminhtml\Listing\Grid
{
    private \M2E\Otto\Model\ResourceModel\Product $listingProductResource;
    private \M2E\Otto\Model\ResourceModel\Account $accountResource;
    private \M2E\Otto\Helper\Url $urlHelper;
    private \M2E\Otto\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product $listingProductResource,
        \M2E\Otto\Model\ResourceModel\Account $accountResource,
        \M2E\Otto\Helper\View $viewHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Otto\Helper\Data $dataHelper,
        \M2E\Otto\Helper\Url $urlHelper,
        \M2E\Otto\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        array $data = []
    ) {
        parent::__construct($urlHelper, $viewHelper, $context, $backendHelper, $dataHelper, $data);

        $this->listingProductResource = $listingProductResource;
        $this->accountResource = $accountResource;
        $this->urlHelper = $urlHelper;
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    /**
     * @ingeritdoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('OttoListingItemsByListingGrid');
    }

    /**
     * @ingeritdoc
     */
    public function getRowUrl($item)
    {
        return $this->getUrl(
            '*/otto_listing/view',
            [
                'id' => $item->getId(),
                'back' => $this->getBackUrl(),
            ]
        );
    }

    /**
     * @return string
     */
    private function getBackUrl(): string
    {
        return $this->urlHelper->makeBackUrlParam('*/otto_listing/index');
    }

    /**
     * @return \M2E\Otto\Block\Adminhtml\Otto\Listing\ItemsByListing\Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $collection = $this->listingCollectionFactory->create();
        $collection->getSelect()->join(
            ['account' => $this->accountResource->getMainTable()],
            'account.id = main_table.account_id',
            ['account_title' => 'title']
        );

        $select = $collection->getConnection()->select();
        $select->from(['lp' => $this->listingProductResource->getMainTable()], [
            'listing_id' => 'listing_id',
            'products_total_count' => new \Zend_Db_Expr('COUNT(lp.id)'),
            'products_active_count' => new \Zend_Db_Expr('COUNT(IF(lp.status = 2, lp.id, NULL))'),
            'products_inactive_count' => new \Zend_Db_Expr('COUNT(IF(lp.status != 2, lp.id, NULL))'),
        ]);
        $select->group('lp.listing_id');

        $collection->getSelect()->joinLeft(
            ['t' => $select],
            'main_table.id=t.listing_id',
            [
                'products_total_count' => 'products_total_count',
                'products_active_count' => 'products_active_count',
                'products_inactive_count' => 'products_inactive_count',
            ]
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @ingeritdoc
     */
    protected function _prepareLayout()
    {
        $this->css->addFile('listing/grid.css');

        return parent::_prepareLayout();
    }

    /**
     * @return array[]
     */
    protected function getColumnActionsItems()
    {
        $backUrl = $this->getBackUrl();

        return [
            'manageProducts' => [
                'caption' => __('Manage'),
                'group' => 'products_actions',
                'field' => 'id',
                'url' => [
                    'base' => '*/otto_listing/view',
                    'params' => [
                        'id' => $this->getId(),
                        'back' => $backUrl,
                    ],
                ],
            ],

            'editTitle' => [
                'caption' => __('Title'),
                'group' => 'edit_actions',
                'field' => 'id',
                'onclick_action' => 'EditListingTitleObj.openPopup',
            ],

            'editStoreView' => [
                'caption' => __('Store View'),
                'group' => 'edit_actions',
                'field' => 'id',
                'onclick_action' => 'EditListingStoreViewObj.openPopup',
            ],

            'editConfiguration' => [
                'caption' => __('Configuration'),
                'group' => 'edit_actions',
                'field' => 'id',
                'url' => [
                    'base' => '*/otto_listing/edit',
                    'params' => ['back' => $backUrl],
                ],
            ],

            'viewLogs' => [
                'caption' => __('Logs & Events'),
                'group' => 'other',
                'field' => \M2E\Otto\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
                'url' => [
                    'base' => '*/otto_log_listing_product/index',
                ],
            ],

            'clearLogs' => [
                'caption' => __('Clear Log'),
                'confirm' => __('Are you sure?'),
                'group' => 'other',
                'field' => 'id',
                'url' => [
                    'base' => '*/listing/clearLog',
                    'params' => [
                        'back' => $backUrl,
                    ],
                ],
            ],

            'delete' => [
                'caption' => __('Delete Listing'),
                'confirm' => __('Are you sure?'),
                'group' => 'other',
                'field' => 'id',
                'url' => [
                    'base' => '*/otto_listing/delete',
                    'params' => ['id' => $this->getId()],
                ],
            ],
        ];
    }

    /**
     * editPartsCompatibilityMode has to be not accessible for not Multi Motors marketplaces
     * @return $this
     */
    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        $this->getColumn('actions')->setData(
            'renderer',
            \M2E\Otto\Block\Adminhtml\Otto\Listing\Grid\Column\Renderer\Action::class
        );

        return $result;
    }

    /**
     * @param string $value
     * @param \M2E\Otto\Model\Listing $row
     * @param Rewrite $column
     * @param bool $isExport
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = \M2E\Otto\Helper\Data::escapeHtml($value);

        $value = <<<HTML
<span id="listing_title_{$row->getId()}">
    {$title}
</span>
HTML;

        $accountTitle = $row->getData('account_title');

        $storeModel = $this->_storeManager->getStore($row->getStoreId());
        $storeView = $this->_storeManager->getWebsite($storeModel->getWebsiteId())->getName();
        if (strtolower($storeView) != 'admin') {
            $storeView .= ' > ' . $this->_storeManager->getGroup($storeModel->getStoreGroupId())->getName();
            $storeView .= ' > ' . $storeModel->getName();
        } else {
            $storeView = __('Admin (Default Values)');
        }

        $account = __('Account');
        $store = __('Magento Store View');

        $value .= <<<HTML
<div>
    <span style="font-weight: bold">{$account}</span>: <span style="color: #505050">{$accountTitle}</span><br/>
    <span style="font-weight: bold">{$store}</span>: <span style="color: #505050">{$storeView}</span>
</div>
HTML;

        return $value;
    }

    /**
     * @param \M2E\Otto\Model\ResourceModel\Listing\Collection $collection
     * @param Rewrite $column
     *
     * @return void
     */
    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'main_table.title LIKE ? OR account.title LIKE ?',
            '%' . $value . '%'
        );
    }

    /**
     * @ingeritdoc
     */
    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        $this->jsUrl->addUrls(
            array_merge(
                $this->dataHelper->getControllerActions('Otto\Listing'),
                $this->dataHelper->getControllerActions('Otto_Listing_Product_Add'),
                $this->dataHelper->getControllerActions('Otto_Log_Listing_Product'),
                $this->dataHelper->getControllerActions('Otto\Template')
            )
        );

        $this->jsUrl->add($this->getUrl('*/listing/edit'), 'listing/edit');

        $this->jsUrl->add($this->getUrl('*/otto_listing_edit/selectStoreView'), 'listing/selectStoreView');
        $this->jsUrl->add($this->getUrl('*/otto_listing_edit/saveStoreView'), 'listing/saveStoreView');

        $this->jsTranslator->add('Edit Listing Title', __('Edit Listing Title'));
        $this->jsTranslator->add('Edit Listing Store View', __('Edit Listing Store View'));
        $this->jsTranslator->add('Listing Title', __('Listing Title'));
        $this->jsTranslator->add(
            'The specified Title is already used for other Listing. Listing Title must be unique.',
            __(
                'The specified Title is already used for other Listing. Listing Title must be unique.'
            )
        );

        $this->jsPhp->addConstants(
            \M2E\Otto\Helper\Data::getClassConstants(\M2E\Otto\Helper\Component\Otto::class)
        );

        $this->js->add(
            <<<JS
    require([
        'Otto/Otto/Listing/Grid',
        'Otto/Listing/EditTitle',
        'Otto/Listing/EditStoreView'
    ], function(){
        window.OttoListingGridObj = new OttoListingGrid('{$this->getId()}');
        window.EditListingTitleObj = new ListingEditListingTitle('{$this->getId()}');
        window.EditListingStoreViewObj = new ListingEditListingStoreView('{$this->getId()}');
    });
JS
        );

        return parent::_toHtml();
    }
}
