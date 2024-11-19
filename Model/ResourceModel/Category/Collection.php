<?php

namespace M2E\Otto\Model\ResourceModel\Category;

/**
 * @method \M2E\Otto\Model\Category getFirstItem()
 * @method \M2E\Otto\Model\Category[] getItems()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Category::class,
            \M2E\Otto\Model\ResourceModel\Category::class
        );
    }
}
