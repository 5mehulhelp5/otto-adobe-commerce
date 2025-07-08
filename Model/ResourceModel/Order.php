<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class Order extends ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_MAGENTO_ORDER_ID = 'magento_order_id';
    public const COLUMN_OTTO_ORDER_ID = 'otto_order_id';
    public const COLUMN_ORDER_STATUS = 'order_status';
    public const COLUMN_PURCHASE_CREATE_DATE = 'purchase_create_date';
    public const COLUMN_SHIPPING_DATE_TO = 'shipping_date_to';
    public const COLUMN_OTTO_ORDER_NUMBER = 'otto_order_number';
    public const COLUMN_MAGENTO_ORDER_CREATION_FAILURE = 'magento_order_creation_failure';
    public const COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT = 'magento_order_creation_fails_count';
    public const COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE = 'magento_order_creation_latest_attempt_date';

    public function _construct(): void
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_ORDER, self::COLUMN_ID);
    }
}
