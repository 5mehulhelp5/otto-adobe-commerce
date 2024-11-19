<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Category\Attribute;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Category\Attribute::class,
            \M2E\Otto\Model\ResourceModel\Category\Attribute::class
        );
    }
}
