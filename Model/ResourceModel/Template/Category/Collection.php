<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Template\Category;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Otto\Template\Category::class,
            \M2E\Otto\Model\ResourceModel\Category::class
        );
    }
}
