<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Synchronization\Log;

/**
 * @method \M2E\Otto\Model\Synchronization\Log[] getItems()
 * @method \M2E\Otto\Model\Synchronization\Log getFirstItem()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Synchronization\Log::class,
            \M2E\Otto\Model\ResourceModel\Synchronization\Log::class
        );
    }
}
