<?php

declare(strict_types=1);

namespace M2E\Otto\Model\OperationHistory;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\OperationHistory $resource;
    private \M2E\Otto\Model\OperationHistoryFactory $operationHistoryFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\OperationHistory $resource,
        \M2E\Otto\Model\OperationHistoryFactory $operationHistoryFactory
    ) {
        $this->resource = $resource;
        $this->operationHistoryFactory = $operationHistoryFactory;
    }

    public function find(int $id): ?\M2E\Otto\Model\OperationHistory
    {
        $model = $this->operationHistoryFactory->create();
        $this->resource->load($model, $id);
        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    public function get(int $id): \M2E\Otto\Model\OperationHistory
    {
        $model = $this->find($id);
        if ($model === null) {
            throw new \M2E\Otto\Model\Exception\Logic('Entity not found by id ' . $id);
        }

        return $model;
    }

    public function clear(\DateTime $borderDate): void
    {
        $minDate = $borderDate->format('Y-m-d H:i:s');

        $this->resource->getConnection()->delete($this->resource->getMainTable(), "start_date <= '$minDate'");
    }
}
