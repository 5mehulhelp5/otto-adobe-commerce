<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Category;

class Attribute extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY_ID = 'category_group_attribute_dictionary_id';
    public const COLUMN_ATTRIBUTE_TITLE = 'attribute_title';
    public const COLUMN_ATTRIBUTE_DESCRIPTION = 'attribute_description';
    public const COLUMN_ATTRIBUTE_TYPE = 'attribute_type';
    public const COLUMN_VALUE_MODE = 'value_mode';
    public const COLUMN_VALUE_RECOMMENDED = 'value_recommended';
    public const COLUMN_VALUE_CUSTOM_VALUE = 'value_custom_value';
    public const COLUMN_VALUE_CUSTOM_ATTRIBUTE = 'value_custom_attribute';

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_CATEGORY_ATTRIBUTES,
            self::COLUMN_ID
        );
    }
}
