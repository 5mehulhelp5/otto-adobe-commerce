<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account;

use M2E\Otto\Block\Adminhtml\Account\Grid as AccountGrid;

class Grid extends AccountGrid
{
    private \M2E\Otto\Model\ResourceModel\Account\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Account\CollectionFactory $collectionFactory,
        \M2E\Otto\Helper\View $viewHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        parent::__construct($viewHelper, $context, $backendHelper, $data);
        $this->collectionFactory = $collectionFactory;
    }

    public function _construct()
    {
        parent::_construct();

        $this->jsPhp->addConstants(
            \M2E\Otto\Helper\Data::getClassConstants(\M2E\Otto\Helper\Component\Otto::class)
        );

        $this->jsTranslator->addTranslations(
            [
                'The specified Title is already used for other Account. Account Title must be unique.' => __(
                    'The specified Title is already used for other Account. Account Title must be unique.'
                ),
                'Be attentive! By Deleting Account you delete all information on it from M2E Otto Server. '
                . 'This will cause inappropriate work of all Accounts\' copies.' => __(
                    'Be attentive! By Deleting Account you delete all information on it from M2E Otto Server. '
                    . 'This will cause inappropriate work of all Accounts\' copies.'
                ),
                'No Customer entry is found for specified ID.' => __(
                    'No Customer entry is found for specified ID.'
                ),
                'If Yes is chosen, you must select at least one Attribute for Product Linking.' => __(
                    'If Yes is chosen, you must select at least one Attribute for Product Linking.'
                ),
                'You should create at least one Response Template.' => __(
                    'You should create at least one Response Template.'
                ),
            ]
        );

        $this->jsUrl->addUrls([
            '*/otto_account/delete/' => $this->getUrl('*/otto_account/delete/'),
        ]);

        $this->js->add(
            <<<JS
    require([
        'Otto/Otto/Account'
    ], function(){
        window.OttoAccountObj = new OttoAccount();
    });
JS
        );
    }

    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'id',
            'filter_index' => 'main_table.id',
        ]);

        $this->addColumn('title', [
            'header' => __('Title / Info'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'title',
            'escape' => true,
            'filter_index' => 'main_table.title',
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        return parent::_prepareColumns();
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        /** @var \M2E\Otto\Model\Account $row */
        $openIdLabel = __('Installation ID');
        $openId = $row->getInstallationId();

        $value = <<<HTML
        <div>
            {$value}<br/>
            <span style="font-weight: bold">{$openIdLabel}</span>:
            <span style="color: #505050">{$openId}</span>
            <br/>
            <br/>
        </div>
HTML;

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $delete = __('Delete');

        return <<<HTML
<div>
    <a class="action-default" href="javascript:" onclick="OttoAccountObj.deleteClick('{$row->getId()}')">
        {$delete}
    </a>
</div>
HTML;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.title LIKE ?', '%' . $value . '%');
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/edit', ['id' => $item->getData('id')]);
    }
}
