<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Synchronization\Log;

class Grid extends \M2E\Otto\Block\Adminhtml\Log\AbstractGrid
{
    private const OPTION_ALL_LOGS = 'all';

    private array $actionsTitles = [];
    private \M2E\Otto\Model\ResourceModel\Synchronization\Log\CollectionFactory $syncLogCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Synchronization\Log\CollectionFactory $syncLogCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Otto\Helper\View $viewHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->syncLogCollectionFactory = $syncLogCollectionFactory;
        parent::__construct($resourceConnection, $viewHelper, $context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $task = $this->getRequest()->getParam('task', '');

        $this->setId(
            'synchronizationLogGrid' . $task . 'Otto',
        );

        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $filters = [];
        if ($task !== null) {
            $filters['task'] = $task;
        }

        $this->setDefaultFilter($filters);

        $this->actionsTitles = \M2E\Otto\Helper\Module\Log::getActionsTitlesByClass(
            \M2E\Otto\Model\Synchronization\Log::class,
        );
    }

    protected function _getLogTypeList(): array
    {
        return [
            \M2E\Otto\Model\Log\AbstractModel::TYPE_WARNING => (string)__('Warning'),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_ERROR => (string)__('Error'),
            \M2E\Otto\Model\Synchronization\Log::TYPE_FATAL_ERROR => (string)__('Fatal Error'),
        ];
    }

    protected function _prepareCollection()
    {
        $collection = $this->syncLogCollectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'create_date',
            [
                'header' => __('Date'),
                'align' => 'left',
                'type' => 'datetime',
                'filter' => \M2E\Otto\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
                'filter_time' => true,
                'format' => \IntlDateFormatter::MEDIUM,
                'index' => 'create_date',
            ],
        );

        $this->addColumn(
            'task',
            [
                'header' => __('Task'),
                'align' => 'left',
                'type' => 'options',
                'index' => 'task',
                'sortable' => false,
                'filter_index' => 'task',
                'filter_condition_callback' => [$this, 'callbackFilterTask'],
                'option_groups' => $this->getActionTitles(),
                'options' => $this->actionsTitles,
            ],
        );

        $this->addColumn(
            'description',
            [
                'header' => __('Message'),
                'align' => 'left',
                'type' => 'text',
                'string_limit' => 350,
                'index' => 'description',
                'filter_index' => 'main_table.description',
                'frame_callback' => [$this, 'callbackColumnDescription'],
            ],
        );

        $this->addColumn(
            'detailed_description',
            [
                'header' => __('Detailed'),
                'align' => 'left',
                'type' => 'text',
                'string_limit' => 65000,
                'index' => 'detailed_description',
                'filter_index' => 'main_table.detailed_description',
                'frame_callback' => [$this, 'callbackColumnDescription'],
            ],
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
                'align' => 'right',
                'type' => 'options',
                'sortable' => false,
                'options' => $this->_getLogTypeList(),
                'frame_callback' => [$this, 'callbackColumnType'],
            ],
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction(): void
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
    }

    /**
     * @param \M2E\Otto\Model\ResourceModel\Synchronization\Log\Collection $collection
     * @param mixed $column
     *
     * @return void
     */
    protected function callbackFilterTask($collection, $column): void
    {
        $value = $column->getFilter()->getValue();

        if (
            $value == null
            || $value == self::OPTION_ALL_LOGS
        ) {
            return;
        }

        $parts = explode('_', $value);
        if (!isset($parts[1])) {
            return;
        }

        $taskCode = $parts[1];

        if ($taskCode != \M2E\Otto\Model\Synchronization\Log::TASK_ALL) {
            $collection->addFieldToFilter('task', $taskCode);
        }
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('*/synchronization_log/grid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function getActionTitles()
    {
        $titles = [];
        foreach ($this->actionsTitles as $value => $label) {
            $titles[] = [
                'label' => $label,
                'value' => \M2E\Otto\Helper\View\Otto::NICK . '_' . $value,
            ];
        }

        $commonTitles = [
            [
                'label' => 'All Integrations',
                'value' => self::OPTION_ALL_LOGS,
            ],
        ];

        return [
            ['label' => __('General'), 'value' => $commonTitles],
            ['label' => __('Task'), 'value' => $titles],
        ];
    }
}
