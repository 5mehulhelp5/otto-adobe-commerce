<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class Category extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_CATEGORY_GROUP_ID = 'category_group_id';
    public const COLUMN_CATEGORY_DICTIONARY_ID = 'category_dictionary_id';
    public const COLUMN_STATE = 'state';
    public const COLUMN_PATH = 'path';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_TOTAL_PRODUCT_ATTRIBUTES = 'total_product_attributes';
    public const COLUMN_USED_PRODUCT_ATTRIBUTES = 'used_product_attributes';
    public const COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES = 'has_required_product_attributes';
    public const COLUMN_IS_DELETED = 'is_deleted';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_CATEGORY, self::COLUMN_ID);
    }
}
