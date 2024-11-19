<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Listing\Wizard\Step;

/**
 * @method \M2E\Otto\Model\Listing\Wizard\Step[] getItems()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Listing\Wizard\Step::class,
            \M2E\Otto\Model\ResourceModel\Listing\Wizard\Step::class
        );
    }
}
