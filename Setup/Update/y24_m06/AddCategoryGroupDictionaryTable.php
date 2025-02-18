<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup as CategoryGroupDictionaryResource;
use Magento\Framework\DB\Ddl\Table;

class AddCategoryGroupDictionaryTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $categoryGroupDictionaryTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_CATEGORY_GROUP_DICTIONARY));

        $categoryGroupDictionaryTable
            ->addColumn(
                CategoryGroupDictionaryResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                CategoryGroupDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                CategoryGroupDictionaryResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                CategoryGroupDictionaryResource::COLUMN_PRODUCT_TITLE_PATTERN,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addIndex('category_group_id', 'category_group_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryGroupDictionaryTable);
    }
}
