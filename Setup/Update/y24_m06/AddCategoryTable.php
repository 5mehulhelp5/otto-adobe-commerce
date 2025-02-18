<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\DB\Ddl\Table;

class AddCategoryTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $categoryTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_CATEGORY));

        $categoryTable->addColumn(
            CategoryResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $categoryTable->addColumn(
            'category_group_id',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_CATEGORY_DICTIONARY_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_STATE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_PATH,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_TOTAL_PRODUCT_ATTRIBUTES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_USED_PRODUCT_ATTRIBUTES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES,
            Table::TYPE_BOOLEAN,
            null,
            ['default' => 0]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $categoryTable->addColumn(
            CategoryResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $categoryTable->addIndex('category_group_dictionary_id', 'category_group_dictionary_id');
        $categoryTable->addIndex('category_dictionary_id', 'category_dictionary_id');
        $categoryTable
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($categoryTable);
    }
}
