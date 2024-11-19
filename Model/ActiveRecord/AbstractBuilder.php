<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ActiveRecord;

use M2E\Otto\Model\ActiveRecord\AbstractModel as T;

abstract class AbstractBuilder
{
    protected T $model;
    protected array $rawData;

    abstract protected function prepareData(): array;

    abstract public function getDefaultData(): array;

    /**
     * @psalm-template T of \M2E\Otto\Model\ActiveRecord\AbstractModel
     * @param T $model
     * @param array $rawData
     *
     * @return T
     */
    public function build(T $model, array $rawData): T
    {
        if (empty($rawData)) {
            return $model;
        }

        $this->model = $model;
        $this->rawData = $rawData;

        $preparedData = $this->prepareData();
        $this->model->addData($preparedData);

        $this->model->save();

        return $this->model;
    }

    public function getModel(): T
    {
        return $this->model;
    }
}
