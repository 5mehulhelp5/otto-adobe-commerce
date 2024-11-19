<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Setup;

/**
 * @method \M2E\Otto\Model\Setup getFirstItem()
 * @method \M2E\Otto\Model\Setup[] getItems()
 * @method \M2E\Otto\Model\Setup getLastItem()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Model\Setup::class,
            \M2E\Otto\Model\ResourceModel\Setup::class
        );
    }
}
