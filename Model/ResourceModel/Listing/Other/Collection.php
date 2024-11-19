<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Listing\Other;

/**
 * @method \M2E\Otto\Model\Listing\Other[] getItems()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Listing\Other::class,
            \M2E\Otto\Model\ResourceModel\Listing\Other::class
        );
    }
}
