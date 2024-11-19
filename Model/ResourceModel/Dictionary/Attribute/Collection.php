<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Dictionary\Attribute;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Dictionary\Attribute::class,
            \M2E\Otto\Model\ResourceModel\Dictionary\Attribute::class
        );
    }
}
