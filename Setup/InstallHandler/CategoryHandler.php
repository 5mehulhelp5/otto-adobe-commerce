<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\InstallHandler;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use M2E\Otto\Model\ResourceModel\Brand as BrandResource;
use M2E\Otto\Model\ResourceModel\Category as CategoryResource;
use M2E\Otto\Model\ResourceModel\Category\Attribute as CategoryAttributeResource;
use M2E\Otto\Model\ResourceModel\Dictionary\Attribute as AttributeDictionaryResource;
use M2E\Otto\Model\ResourceModel\Dictionary\Category as CategoryDictionaryResource;
use M2E\Otto\Model\ResourceModel\Dictionary\CategoryGroup as CategoryGroupDictionaryResource;
use Magento\Framework\DB\Ddl\Table;

class CategoryHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Otto\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installCategoryDictionaryTable($setup);
        $this->installCategoryGroupDictionaryTable($setup);
        $this->installCategoryGroupAttributeDictionaryTable($setup);
        $this->installCategoryTable($setup);
        $this->installCategoryAttributesTable($setup);
        $this->installBrandTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installCategoryDictionaryTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_DICTIONARY));

        $table
            ->addColumn(
                CategoryDictionaryResource::COLUMN_ID,
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
                CategoryDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                CategoryDictionaryResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addIndex('category_group_id', 'category_group_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installCategoryGroupDictionaryTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_GROUP_DICTIONARY));

        $table
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
                ['nullable' => false,]
            )
            ->addIndex('category_group_id', 'category_group_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installCategoryGroupAttributeDictionaryTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY));

        $table
            ->addColumn(
                AttributeDictionaryResource::COLUMN_ID,
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
                AttributeDictionaryResource::COLUMN_CATEGORY_GROUP_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                30,
                ['nullable' => false]
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_IS_REQUIRED,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_IS_MULTIPLE_SELECTED,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_ALLOWED_VALUES,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_EXAMPLE_VALUES,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_RELEVANCE,
                Table::TYPE_TEXT,
                30,
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_REQUIRED_MEDIA_TYPES,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
            )
            ->addColumn(
                AttributeDictionaryResource::COLUMN_UNIT,
                Table::TYPE_TEXT,
                30,
            )
            ->addIndex('category_group_id', 'category_group_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installCategoryTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY));

        $table->addColumn(
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
        $table->addColumn(
            CategoryResource::COLUMN_CATEGORY_GROUP_ID,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $table->addColumn(
            CategoryResource::COLUMN_STATE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false,]
        );
        $table->addColumn(
            CategoryResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false,]
        );
        $table->addColumn(
            CategoryResource::COLUMN_TOTAL_PRODUCT_ATTRIBUTES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $table->addColumn(
            CategoryResource::COLUMN_USED_PRODUCT_ATTRIBUTES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $table->addColumn(
            CategoryResource::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES,
            Table::TYPE_BOOLEAN,
            null,
            ['default' => 0]
        );
        $table->addColumn(
            CategoryResource::COLUMN_IS_DELETED,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $table->addColumn(
            CategoryResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        );
        $table->addColumn(
            CategoryResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null]
        )
              ->addIndex('category_group_id', 'category_group_id')
              ->setOption('type', 'INNODB')
              ->setOption('charset', 'utf8')
              ->setOption('collate', 'utf8_general_ci')
              ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installCategoryAttributesTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_CATEGORY_ATTRIBUTES));

        $table
            ->addColumn(
                CategoryAttributeResource::COLUMN_ID,
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
                CategoryAttributeResource::COLUMN_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                CategoryAttributeResource::COLUMN_ATTRIBUTE_TYPE,
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                CategoryAttributeResource::COLUMN_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY_ID,
                Table::TYPE_TEXT,
                50,
            )
            ->addColumn(
                CategoryAttributeResource::COLUMN_ATTRIBUTE_TITLE,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                CategoryAttributeResource::COLUMN_ATTRIBUTE_DESCRIPTION,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                CategoryAttributeResource::COLUMN_VALUE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                CategoryAttributeResource::COLUMN_VALUE_RECOMMENDED,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE
            )
            ->addColumn(
                CategoryAttributeResource::COLUMN_VALUE_CUSTOM_VALUE,
                Table::TYPE_TEXT,
                255,
            )
            ->addColumn(
                CategoryAttributeResource::COLUMN_VALUE_CUSTOM_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
            )
            ->addIndex(
                'category_id',
                CategoryAttributeResource::COLUMN_CATEGORY_ID,
            )
            ->addIndex('category_group_attribute_dictionary_id', 'category_group_attribute_dictionary_id')
            ->addIndex('category_id', 'category_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installBrandTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_BRAND));

        $table
            ->addColumn(
                BrandResource::COLUMN_ID,
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
                BrandResource::COLUMN_BRAND_ID,
                Table::TYPE_TEXT,
                100,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                BrandResource::COLUMN_NAME,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false,]
            )
            ->addColumn(
                BrandResource::COLUMN_IS_USABLE,
                Table::TYPE_BOOLEAN,
                255,
                ['nullable' => false,]
            )
            ->addIndex('brand_id', 'brand_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
