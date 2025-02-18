<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class ExternalChange extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SKU = 'sku';

    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct()
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_EXTERNAL_CHANGE, self::COLUMN_ID);
    }
}
