<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category;

use M2E\Otto\Model\Category;
use M2E\Otto\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Grid extends \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private CategoryCollectionFactory $categoryCollectionFactory;

    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoTemplateCategoryGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
    }

    protected function _prepareCollection()
    {
        $collection = $this->categoryCollectionFactory->create();

        $collection->getSelect()->where(
            'main_table.state != ?',
            Category::DRAFT_STATE
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'align' => 'left',
                'type' => 'text',
                'escape' => true,
                'index' => 'title'
            ]
        );

        $this->addColumn(
            'total_attributes',
            [
                'header' => __('Attributes: Total'),
                'align' => 'left',
                'type' => 'text',
                'width' => '100px',
                'index' => 'total_product_attributes',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'used_attributes',
            [
                'header' => __('Attributes: Used'),
                'align' => 'left',
                'type' => 'text',
                'width' => '100px',
                'index' => 'used_product_attributes',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'actions',
            [
                'header' => __('Actions'),
                'align' => 'left',
                'width' => '70px',
                'type' => 'action',
                'index' => 'actions',
                'filter' => false,
                'sortable' => false,
                'renderer' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/otto_category/view',
                            'params' => [
                                'category_id' => '$id',
                            ],
                        ],
                        'field' => 'id',
                    ],
                ],
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Remove'),
                'url' => $this->getUrl('*/otto_category/delete'),
                'confirm' => __('Are you sure?'),
            ]
        );

        return parent::_prepareMassaction();
    }

    protected function callbackFilterPath($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.path LIKE ?', '%' . $value . '%');
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }
}
