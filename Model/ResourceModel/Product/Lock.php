<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Product;

class Lock extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_PRODUCT_ID = 'product_id';
    public const COLUMN_INITIATOR = 'initiator';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_LOCK,
            self::COLUMN_ID
        );
    }
}
