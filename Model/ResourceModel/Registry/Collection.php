<?php

namespace M2E\Otto\Model\ResourceModel\Registry;

/**
 * Class \M2E\Otto\Model\ResourceModel\Registry\Collection
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        $this->_init(
            \M2E\Otto\Model\Registry::class,
            \M2E\Otto\Model\ResourceModel\Registry::class
        );
    }
}
