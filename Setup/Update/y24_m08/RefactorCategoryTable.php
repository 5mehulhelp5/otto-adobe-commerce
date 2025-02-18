<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m08;

use M2E\Otto\Helper\Module\Database\Tables;

class RefactorCategoryTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_CATEGORY);

        $modifier->dropColumn('category_dictionary_id', true, false);
        $modifier->dropColumn('path', true, false);
        $modifier->addColumn(
            \M2E\Otto\Model\ResourceModel\Category::COLUMN_IS_DELETED,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            \M2E\Otto\Model\ResourceModel\Category::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES,
            false,
            false
        );
        $modifier->renameColumn(
            'category_group_dictionary_id',
            'category_group_id',
            true,
            false
        );

        $modifier->commit();
    }
}
