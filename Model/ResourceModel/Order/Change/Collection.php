<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Order\Change;

/**
 * @method \M2E\Otto\Model\Order\Change[] getItems()
 * @method \M2E\Otto\Model\Order\Change getFirstItem()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Order\Change::class,
            \M2E\Otto\Model\ResourceModel\Order\Change::class
        );
    }
}
