<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Template;

class Shipping extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_SHIPPING_PROFILE_ID = 'shipping_profile_id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_IS_CUSTOM_TEMPLATE = 'is_custom_template';
    public const COLUMN_HANDLING_TIME = 'handling_time';
    public const COLUMN_HANDLING_TIME_MODE = 'handling_time_mode';
    public const COLUMN_HANDLING_TIME_ATTRIBUTE = 'handling_time_attribute';
    public const COLUMN_TRANSPORT_TIME = 'transport_time';
    public const COLUMN_ORDER_CUTOFF = 'order_cutoff';
    public const COLUMN_WORKING_DAYS = 'working_days';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_IS_DELETED = 'is_deleted';

    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_TEMPLATE_SHIPPING,
            self::COLUMN_ID
        );
    }
}
