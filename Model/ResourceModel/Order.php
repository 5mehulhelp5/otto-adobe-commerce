<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class Order extends ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_MAGENTO_ORDER_ID = 'magento_order_id';
    public const COLUMN_OTTO_ORDER_ID = 'otto_order_id';
    public const COLUMN_OTTO_ORDER_NUMBER = 'otto_order_number';

    public function _construct(): void
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_ORDER, self::COLUMN_ID);
    }
}
