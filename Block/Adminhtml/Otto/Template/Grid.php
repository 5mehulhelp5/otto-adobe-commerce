<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template;

use M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractGrid;
use Magento\Framework\DB\Select;

class Grid extends AbstractGrid
{
    private \M2E\Otto\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\Otto\Model\ResourceModel\Template\SellingFormat\CollectionFactory $sellingCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Template\Synchronization\CollectionFactory $syncCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Template\Shipping\CollectionFactory $shippingCollectionFactory;
    private \M2E\Otto\Model\ResourceModel\Account $accountResource;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Account $accountResource,
        \M2E\Otto\Model\ResourceModel\Template\SellingFormat\CollectionFactory $sellingCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Template\Synchronization\CollectionFactory $syncCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Template\Shipping\CollectionFactory $shippingCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->wrapperCollectionFactory = $wrapperCollectionFactory;
        $this->resourceConnection = $resourceConnection;

        parent::__construct($context, $backendHelper, $data);
        $this->sellingCollectionFactory = $sellingCollectionFactory;
        $this->descriptionCollectionFactory = $descriptionCollectionFactory;
        $this->syncCollectionFactory = $syncCollectionFactory;
        $this->shippingCollectionFactory = $shippingCollectionFactory;
        $this->accountResource = $accountResource;
    }

    public function _construct()
    {
        parent::_construct();

        $this->css->addFile('policy/grid.css');

        // Initialization block
        // ---------------------------------------
        $this->setId('ottoTemplateGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        // Prepare selling format collection
        // ---------------------------------------
        $collectionSellingFormat = $this->sellingCollectionFactory->create();
        $collectionSellingFormat->getSelect()->reset(Select::COLUMNS);
        $collectionSellingFormat->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('NULL as `account_title`'),
                new \Zend_Db_Expr(
                    '\'' . \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SELLING_FORMAT . '\' as `nick`'
                ),
                'create_date',
                'update_date',
                new \Zend_Db_Expr('NULL as `is_deleted`'),
            ]
        );

        // ---------------------------------------

        // Prepare synchronization collection
        // ---------------------------------------
        $collectionSynchronization = $this->syncCollectionFactory->create();
        $collectionSynchronization->getSelect()->reset(Select::COLUMNS);
        $collectionSynchronization->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('NULL as `account_title`'),
                new \Zend_Db_Expr(
                    '\'' . \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SYNCHRONIZATION . '\' as `nick`'
                ),
                'create_date',
                'update_date',
                new \Zend_Db_Expr('NULL as `is_deleted`'),
            ]
        );
        // ---------------------------------------

        // Prepare description collection
        // ---------------------------------------
        $collectionDescription = $this->descriptionCollectionFactory->create();
        $collectionDescription->getSelect()->reset(Select::COLUMNS);
        $collectionDescription->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('NULL as `account_title`'),
                new \Zend_Db_Expr(
                    '\'' . \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_DESCRIPTION . '\' as `nick`'
                ),
                'create_date',
                'update_date',
                new \Zend_Db_Expr('NULL as `is_deleted`'),
            ]
        );

        // ---------------------------------------

        ///Prepare Shipping collection
        $collectionShipping = $this->shippingCollectionFactory->create();
        $collectionShipping->getSelect()->reset(Select::COLUMNS);
        $collectionShipping->getSelect()->join(
            ['account' => $this->accountResource->getMainTable()],
            sprintf(
                'account.%s = main_table.%s',
                \M2E\Otto\Model\ResourceModel\Account::COLUMN_ID,
                \M2E\Otto\Model\ResourceModel\Template\Shipping::COLUMN_ACCOUNT_ID
            ),
            []
        );

        $collectionShipping->getSelect()->columns(
            [
                'id as template_id',
                'title',
                new \Zend_Db_Expr('account.title as `account_title`'),
                new \Zend_Db_Expr(
                    '\'' . \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SHIPPING . '\' as `nick`'
                ),
                'create_date',
                'update_date',
                'is_deleted',
            ]
        );
        //// ---------------------------------------

        // Prepare union select
        // ---------------------------------------
        $unionSelect = $this->resourceConnection->getConnection()->select();
        $unionSelect->union([
            $collectionSellingFormat->getSelect(),
            $collectionSynchronization->getSelect(),
            $collectionDescription->getSelect(),
            $collectionShipping->getSelect(),
        ]);
        // ---------------------------------------

        // Prepare result collection
        // ---------------------------------------
        $resultCollection = $this->wrapperCollectionFactory->create();
        $resultCollection->setConnection($this->resourceConnection->getConnection());
        $resultCollection->getSelect()->reset()->from(
            ['main_table' => $unionSelect],
            ['template_id', 'title', 'account_title', 'nick', 'create_date', 'update_date', 'is_deleted']
        );
        // ---------------------------------------

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', [
            'header' => __('Title'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'title',
            'escape' => true,
            'filter_index' => 'main_table.title',
            'frame_callback' => [$this, 'callbackColumnTitle'],
        ]);

        $options = [
            \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SELLING_FORMAT => __('Selling'),
            \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_DESCRIPTION => __('Description'),
            \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SYNCHRONIZATION => __('Synchronization'),
            \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SHIPPING => __('Shipping'),
        ];
        $this->addColumn('nick', [
            'header' => __('Type'),
            'align' => 'left',
            'type' => 'options',
            'width' => '100px',
            'sortable' => false,
            'index' => 'nick',
            'filter_index' => 'main_table.nick',
            'options' => $options,
        ]);

        $this->addColumn('account', [
            'header' => $this->__('Account'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'account_title',
            'filter_index' => 'account_title',
            'filter_condition_callback' => [$this, 'callbackFilterAccount'],
            'frame_callback' => [$this, 'callbackColumnAccount'],
        ]);

        $this->addColumn('create_date', [
            'header' => (string)__('Creation Date'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'datetime',
            'filter' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
            'filter_time' => true,
            'format' => \IntlDateFormatter::MEDIUM,
            'index' => 'create_date',
            'filter_index' => 'main_table.create_date',
        ]);

        $this->addColumn('update_date', [
            'header' => (string)__('Update Date'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'datetime',
            'filter' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
            'filter_time' => true,
            'format' => \IntlDateFormatter::MEDIUM,
            'index' => 'update_date',
            'filter_index' => 'main_table.update_date',
        ]);

        $this->addColumn('actions', [
            'header' => __('Actions'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'action',
            'index' => 'actions',
            'filter' => false,
            'sortable' => false,
            'renderer' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'getter' => 'getTemplateId',
            'actions' => [
                [
                    'caption' => __('Edit'),
                    'url' => [
                        'base' => '*/otto_template/edit',
                        'params' => [
                            'nick' => '$nick',
                        ],
                    ],
                    'field' => 'id',
                ],
                [
                    'caption' => __('Delete'),
                    'class' => 'action-default scalable add primary policy-delete-btn',
                    'url' => [
                        'base' => '*/otto_template/delete',
                        'params' => [
                            'nick' => '$nick',
                        ],
                    ],
                    'field' => 'id',
                    'confirm' => __('Are you sure?'),
                ],
            ],
        ]);

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/templateGrid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return $this->getUrl(
            '*/otto_template/edit',
            [
                'id' => $item->getData('template_id'),
                'nick' => $item->getData('nick'),
                'back' => 1,
            ]
        );
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_deleted') === 1) {
            $value .= ' <span style="color: red;"> (Removed)</span>';
        }

        return $value;
    }

    public function callbackColumnAccount($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return $this->__('Any');
        }

        return $value;
    }

    protected function callbackFilterAccount($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('account_title LIKE ?', '%' . $value . '%');
    }
}
