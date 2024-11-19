<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Account;

/**
 * @method \M2E\Otto\Model\Account[] getItems()
 * @method \M2E\Otto\Model\Account getFirstItem()
 */
class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();

        $this->_init(
            \M2E\Otto\Model\Account::class,
            \M2E\Otto\Model\ResourceModel\Account::class
        );
    }
}
