<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Product\Lock;

/**
 * @method \M2E\Otto\Model\Product getFirstItem()
 * @method \M2E\Otto\Model\Product[] getItems()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Product\Lock::class,
            \M2E\Otto\Model\ResourceModel\Product\Lock::class
        );
    }
}
