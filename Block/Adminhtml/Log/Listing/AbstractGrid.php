<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Log\Listing;

abstract class AbstractGrid extends \M2E\Otto\Block\Adminhtml\Log\AbstractGrid
{
    protected \M2E\Otto\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory;
    protected \M2E\Otto\Model\ResourceModel\Collection\CustomFactory $customCollectionFactory;
    private \M2E\Otto\Model\Config\Manager $config;
    protected \M2E\Otto\Helper\Data $dataHelper;

    public function __construct(
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
        $this->config = $config;
        $this->wrapperCollectionFactory = $wrapperCollectionFactory;
        $this->customCollectionFactory = $customCollectionFactory;
        $this->dataHelper = $dataHelper;

        parent::__construct($resourceConnection, $viewHelper, $context, $backendHelper, $data);
    }

    abstract protected function getViewMode();

    abstract protected function getLogHash($type);

    protected function addMaxAllowedLogsCountExceededNotification($date)
    {
        $notification = \M2E\Otto\Helper\Data::escapeJs(
            (string)__(
                'Using a Grouped View Mode, the logs records which are not older than %date are
            displayed here in order to prevent any possible Performance-related issues.',
                ['date' => $this->_localeDate->formatDate($date, \IntlDateFormatter::MEDIUM, true)],
            )
        );

        $this->js->add("Otto.formData.maxAllowedLogsCountExceededNotification = '{$notification}';");
    }

    protected function getMaxLastHandledRecordsCount()
    {
        return $this->config->getGroupValue(
            '/logs/grouped/',
            'max_records_count'
        );
    }

    public function getRowUrl($item)
    {
        return false;
    }
}
