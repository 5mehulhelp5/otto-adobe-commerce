<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\AttributeMapping;

class Pair extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_CHANNEL_ATTRIBUTE_TITLE = 'channel_attribute_title';
    public const COLUMN_CHANNEL_ATTRIBUTE_CODE = 'channel_attribute_code';
    public const COLUMN_MAGENTO_ATTRIBUTE_CODE = 'magento_attribute_code';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_ATTRIBUTE_MAPPING, self::COLUMN_ID);
    }
}
