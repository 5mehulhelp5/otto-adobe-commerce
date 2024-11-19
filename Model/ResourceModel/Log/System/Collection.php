<?php

namespace M2E\Otto\Model\ResourceModel\Log\System;

/**
 * Class \M2E\Otto\Model\ResourceModel\Log\System\Collection
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Log\System::class,
            \M2E\Otto\Model\ResourceModel\Log\System::class
        );
    }

    //########################################
}
