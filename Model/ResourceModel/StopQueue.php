<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class StopQueue extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_IS_PROCESSED = 'is_processed';
    public const COLUMN_REQUEST_DATA = 'request_data';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_STOP_QUEUE,
            self::COLUMN_ID
        );
    }
}
