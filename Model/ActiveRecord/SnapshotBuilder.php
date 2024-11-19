<?php

namespace M2E\Otto\Model\ActiveRecord;

class SnapshotBuilder
{
    protected AbstractModel $model;

    /**
     * @param AbstractModel $model
     */
    public function setModel($model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): AbstractModel
    {
        return $this->model;
    }

    public function getSnapshot()
    {
        $data = $this->getModel()->getData();

        foreach ($data as &$value) {
            (null !== $value && !is_array($value)) && $value = (string)$value;
        }

        return $data;
    }
}
