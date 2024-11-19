<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class Tag extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ERROR_CODE = 'error_code';
    public const COLUMN_TEXT = 'text';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct()
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_TAG, self::COLUMN_ID);
    }
}
