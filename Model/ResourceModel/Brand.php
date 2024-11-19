<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class Brand extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_BRAND_ID = 'brand_id';
    public const COLUMN_NAME = 'name';
    public const COLUMN_IS_USABLE = 'is_usable';

    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_BRAND, self::COLUMN_ID);
    }
}
