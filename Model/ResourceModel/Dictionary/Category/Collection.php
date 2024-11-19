<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Dictionary\Category;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Dictionary\Category::class,
            \M2E\Otto\Model\ResourceModel\Dictionary\Category::class
        );
    }
}
