<?php

namespace M2E\Otto\Model\ResourceModel\Dictionary;

class CategoryGroup extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_CATEGORY_GROUP_ID = 'category_group_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_PRODUCT_TITLE_PATTERN = 'product_title_pattern';

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_CATEGORY_GROUP_DICTIONARY,
            self::COLUMN_ID
        );
    }
}
