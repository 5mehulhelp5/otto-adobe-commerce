<?php

namespace M2E\Otto\Model\ResourceModel\Processing;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        $this->_init(
            \M2E\Otto\Model\Processing::class,
            \M2E\Otto\Model\ResourceModel\Processing::class
        );
    }
}
