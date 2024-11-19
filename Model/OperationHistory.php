<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Helper\Data as Helper;

class OperationHistory extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    private ?self $object = null;
    private \M2E\Otto\Helper\Module\Exception $exceptionHelper;
    private \M2E\Otto\Model\OperationHistoryFactory $operationHistoryFactory;
    private \M2E\Otto\Model\OperationHistory\Repository $repository;

    public function __construct(
        \M2E\Otto\Helper\Module\Exception $exceptionHelper,
        OperationHistoryFactory $operationHistoryFactory,
        \M2E\Otto\Model\OperationHistory\Repository $repository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);

        $this->exceptionHelper = $exceptionHelper;
        $this->operationHistoryFactory = $operationHistoryFactory;
        $this->repository = $repository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\Otto\Model\ResourceModel\OperationHistory::class);
    }

    //########################################

    /**
     * @param self|string|int $value
     *
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function setObject($value): self
    {
        if (is_object($value)) {
            $this->object = $value;
        } else {
            $object = $this->repository->find($value);
            if ($object === null) {
                $this->object = null;
            } else {
                $this->object = $object;
            }
        }

        return $this;
    }

    public function getObject(): ?self
    {
        return $this->object;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getParentObject(string $nick = null): ?self
    {
        if ($this->getObject()->getData('parent_id') === null) {
            return null;
        }

        $parentId = (int)$this->getObject()->getData('parent_id');
        /** @var self $parentObject */
        $parentObject = $this->repository->get($parentId);

        if ($nick === null) {
            return $parentObject;
        }

        while ($parentObject->getData('nick') != $nick) {
            $parentId = $parentObject->getData('parent_id');
            if ($parentId === null) {
                return null;
            }

            /** @var self $parentObject */
            $parentObject = $this->repository->get($parentId);
        }

        return $parentObject;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     * @throws \Exception
     */
    public function start($nick, $parentId = null, $initiator = Helper::INITIATOR_UNKNOWN, array $data = []): bool
    {
        $data = [
            'nick' => $nick,
            'parent_id' => $parentId,
            'data' => \M2E\Otto\Helper\Json::encode($data),
            'initiator' => $initiator,
            'start_date' => \M2E\Otto\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
        ];

        $this->object = $this->operationHistoryFactory->create()
            ->setData($data)
            ->save();

        return true;
    }

    /**
     * @throws \Exception
     */
    public function stop(): bool
    {
        if ($this->object === null || $this->object->getData('end_date')) {
            return false;
        }

        $this->object->setData(
            'end_date',
            \M2E\Otto\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
        )->save();

        return true;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function setContentData($key, $value): bool
    {
        if ($this->object === null) {
            return false;
        }

        $data = [];
        if ($this->object->getData('data') != '') {
            $data = \M2E\Otto\Helper\Json::decode($this->object->getData('data'));
        }

        $data[$key] = $value;
        $this->object->setData(
            'data',
            \M2E\Otto\Helper\Json::encode($data)
        )->save();

        return true;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function addContentData($key, $value): bool
    {
        $existedData = $this->getContentData($key);

        if ($existedData === null) {
            is_array($value) ? $existedData = [$value] : $existedData = $value;

            return $this->setContentData($key, $existedData);
        }

        is_array($existedData) ? $existedData[] = $value : $existedData .= $value;

        return $this->setContentData($key, $existedData);
    }

    /**
     * @param $key
     *
     * @return mixed|null
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function getContentData($key)
    {
        if ($this->object === null) {
            return null;
        }

        if ($this->object->getData('data') == '') {
            return null;
        }

        $data = \M2E\Otto\Helper\Json::decode($this->object->getData('data'));

        if (isset($data[$key])) {
            return $data[$key];
        }

        return null;
    }

    public function makeShutdownFunction()
    {
        if ($this->object === null) {
            return false;
        }

        $objectId = $this->object->getId();
        register_shutdown_function(function () use ($objectId) {
            $error = error_get_last();
            if ($error === null || !in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
                return;
            }

            $object = $this->operationHistoryFactory->create();
            $object->setObject($objectId);

            if (!$object->stop()) {
                return;
            }

            $collection = $object->getCollection()->addFieldToFilter('parent_id', $objectId);
            if ($collection->getSize()) {
                return;
            }

            $stackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $object->setContentData('fatal_error', [
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'trace' => $this->exceptionHelper->getFatalStackTraceInfo($stackTrace),
            ]);
        });

        return true;
    }

    //########################################

    public function getDataInfo($nestingLevel = 0)
    {
        if ($this->object === null) {
            return null;
        }

        $offset = str_repeat(' ', $nestingLevel * 7);
        $separationLine = str_repeat('#', 80 - strlen($offset));

        $nick = strtoupper($this->getObject()->getData('nick'));

        $contentData = (array)\M2E\Otto\Helper\Json::decode(
            $this->getObject()->getData('data')
        );
        $contentData = preg_replace(
            '/^/m',
            "{$offset}",
            print_r($contentData, true)
        );

        return <<<INFO
{$offset}{$nick}
{$offset}Start Date: {$this->getObject()->getData('start_date')}
{$offset}End Date: {$this->getObject()->getData('end_date')}
{$offset}Total Time: {$this->getTotalTime()}

{$offset}{$separationLine}
{$contentData}
{$offset}{$separationLine}

INFO;
    }

    public function getFullDataInfo($nestingLevel = 0)
    {
        if ($this->object === null) {
            return null;
        }

        $dataInfo = $this->getDataInfo($nestingLevel);

        $childObjects = $this->getCollection()
                             ->addFieldToFilter('parent_id', $this->getObject()->getId())
                             ->setOrder('start_date', 'ASC');

        $childObjects->getSize() > 0 && $nestingLevel++;

        foreach ($childObjects as $item) {
            $object = $this->operationHistoryFactory->create();
            $object->setObject($item);

            $dataInfo .= $object->getFullDataInfo($nestingLevel);
        }

        return $dataInfo;
    }

    // ---------------------------------------

    public function getExecutionInfo($nestingLevel = 0)
    {
        if ($this->object === null) {
            return null;
        }

        $offset = str_repeat(' ', $nestingLevel * 5);

        return <<<INFO
{$offset}<b>{$this->getObject()->getData('nick')} ## {$this->getObject()->getData('id')}</b>
{$offset}start date: {$this->getObject()->getData('start_date')}
{$offset}end date:   {$this->getObject()->getData('end_date')}
{$offset}total time: {$this->getTotalTime()}
<br>
INFO;
    }

    public function getExecutionTreeUpInfo()
    {
        if ($this->object === null) {
            return null;
        }

        $extraParent = $this->getObject();
        $executionTree[] = $extraParent;

        while ($parentId = $extraParent->getData('parent_id')) {
            $extraParent = $this->repository->get($parentId);
            $executionTree[] = $extraParent;
        }

        $info = '';
        $executionTree = array_reverse($executionTree);

        foreach ($executionTree as $nestingLevel => $item) {
            $object = $this->operationHistoryFactory->create();
            $object->setObject($item);

            $info .= $object->getExecutionInfo($nestingLevel);
        }

        return $info;
    }

    public function getExecutionTreeDownInfo($nestingLevel = 0)
    {
        if ($this->object === null) {
            return null;
        }

        $info = $this->getExecutionInfo($nestingLevel);

        $childObjects = $this->getCollection()
                             ->addFieldToFilter('parent_id', $this->getObject()->getId())
                             ->setOrder('start_date', 'ASC');

        if ($childObjects->getSize() > 0) {
            $nestingLevel++;
        }

        foreach ($childObjects as $item) {
            $object = $this->operationHistoryFactory->create();
            $object->setObject($item);

            $info .= $object->getExecutionTreeDownInfo($nestingLevel);
        }

        return $info;
    }

    // ---------------------------------------

    protected function getTotalTime(): string
    {
        $endDateTimestamp = \M2E\Otto\Helper\Date::createDateGmt(
            $this->getObject()->getData('end_date')
        )->getTimestamp();

        $startDateTimestamp = \M2E\Otto\Helper\Date::createDateGmt(
            $this->getObject()->getData('start_date')
        )->getTimestamp();

        $totalTime = $endDateTimestamp - $startDateTimestamp;

        if ($totalTime < 0) {
            return 'n/a';
        }

        $minutes = (int)($totalTime / 60);
        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }

        $seconds = $totalTime - (int)$minutes * 60;
        if ($seconds < 10) {
            $seconds = '0' . $seconds;
        }

        return "$minutes:$seconds";
    }
}
