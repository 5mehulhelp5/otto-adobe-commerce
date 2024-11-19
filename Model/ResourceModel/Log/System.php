<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Log;

class System extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_CLASS = 'class';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_DETAILED_DESCRIPTION = 'detailed_description';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_SYSTEM_LOG, 'id');
    }
}
