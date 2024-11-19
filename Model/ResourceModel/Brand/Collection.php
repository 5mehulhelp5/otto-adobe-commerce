<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Brand;

/**
 * @method \M2E\Otto\Model\Brand getFirstItem()
 * @method \M2E\Otto\Model\Brand[] getItems()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Brand::class,
            \M2E\Otto\Model\ResourceModel\Brand::class
        );
    }
}
