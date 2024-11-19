<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Template\Shipping;

/**
 * @method \M2E\Otto\Model\Template\Shipping[] getItems()
 * @method \M2E\Otto\Model\Template\Shipping getFirstItem()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Template\Shipping::class,
            \M2E\Otto\Model\ResourceModel\Template\Shipping::class
        );
    }
}
