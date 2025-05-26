<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus\Task\Server\Status;

use M2E\Otto\Model\HealthStatus\Task\IssueType;
use M2E\Otto\Model\HealthStatus\Task\Result as TaskResult;

class SystemLogs extends IssueType
{
    private const COUNT_CRITICAL_LEVEL = 1500;
    private const COUNT_WARNING_LEVEL = 500;
    private const SEE_TO_BACK_INTERVAL = 3600;

    /** @var \M2E\Otto\Model\HealthStatus\Task\Result\Factory */
    private $resultFactory;
    /** @var \M2E\Otto\Model\ActiveRecord\Factory */
    private $activeRecordFactory;
    /** @var \Magento\Framework\UrlInterface */
    private $urlBuilder;

    public function __construct(
        \M2E\Otto\Model\HealthStatus\Task\Result\Factory $resultFactory,
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        parent::__construct();
        $this->resultFactory = $resultFactory;
        $this->activeRecordFactory = $activeRecordFactory;
        $this->urlBuilder = $urlBuilder;
    }

    public function process()
    {
        $exceptionsCount = $this->getExceptionsCountByBackInterval(self::SEE_TO_BACK_INTERVAL);

        $result = $this->resultFactory->create($this);
        $result->setTaskResult(TaskResult::STATE_SUCCESS);
        $result->setTaskData($exceptionsCount);

        if ($exceptionsCount >= self::COUNT_WARNING_LEVEL) {
            $result->setTaskResult(TaskResult::STATE_WARNING);
            $result->setTaskMessage(
                __(
                    '%extension_title has recorded <b>%exception_count</b> messages to the System Log during the ' .
                    'last hour. <a target="_blank" href="%url">Click here</a> for the details.',
                    [
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                        'exception_count' => $exceptionsCount,
                        'url' => $this->urlBuilder->getUrl('m2e_otto/synchronization_log/index')
                    ]
                )
            );
        }

        if ($exceptionsCount >= self::COUNT_CRITICAL_LEVEL) {
            $result->setTaskResult(TaskResult::STATE_CRITICAL);
            $result->setTaskMessage(
                __(
                    '%extension_title has recorded <b>%exception_count</b> messages to the System Log ' .
                    'during the last hour. <a href="%url">Click here</a> for the details.',
                    [
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                        'exception_count' => $exceptionsCount,
                        'url' => $this->urlBuilder->getUrl('m2e_otto/synchronization_log/index')
                    ]
                )
            );
        }

        return $result;
    }

    private function getExceptionsCountByBackInterval($inSeconds)
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify("- {$inSeconds} seconds");

        $collection = $this->activeRecordFactory->getObject('Log\System')->getCollection();
        $collection->addFieldToFilter('type', ['neq' => '\\' . \M2E\Core\Model\Exception\Connection::class]);
        $collection->addFieldToFilter('type', ['nlike' => '%Logging%']);
        $collection->addFieldToFilter('create_date', ['gt' => $date->format('Y-m-d H:i:s')]);

        return $collection->getSize();
    }
}
