<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Config;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        $this->_init(
            \M2E\Otto\Model\Config::class,
            \M2E\Otto\Model\ResourceModel\Config::class
        );
    }
}
