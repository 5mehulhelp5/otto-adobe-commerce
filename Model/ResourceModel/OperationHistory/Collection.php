<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\OperationHistory;

/**
 * Class \M2E\Otto\Model\ResourceModel\OperationHistory\Collection
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    //########################################

    public function _construct()
    {
        $this->_init(
            \M2E\Otto\Model\OperationHistory::class,
            \M2E\Otto\Model\ResourceModel\OperationHistory::class
        );
    }

    //########################################
}
