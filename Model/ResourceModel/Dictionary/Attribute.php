<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Dictionary;

class Attribute extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_CATEGORY_GROUP_ID = 'category_group_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_IS_REQUIRED = 'is_required';
    public const COLUMN_IS_MULTIPLE_SELECTED = 'is_multiple_selected';
    public const COLUMN_ALLOWED_VALUES = 'allowed_values';
    public const COLUMN_EXAMPLE_VALUES = 'example_values';
    public const COLUMN_RELEVANCE = 'relevance';
    public const COLUMN_REQUIRED_MEDIA_TYPES = 'required_media_types';
    public const COLUMN_UNIT = 'unit';

    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY,
            self::COLUMN_ID
        );
    }
}
