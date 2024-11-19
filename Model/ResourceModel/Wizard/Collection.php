<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Wizard;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct()
    {
        $this->_init(
            \M2E\Otto\Model\Wizard::class,
            \M2E\Otto\Model\ResourceModel\Wizard::class
        );
    }
}
