<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Dictionary\Attribute as AttributeDictionaryResource;
use Magento\Framework\DB\Ddl\Table;

class AddAttributeDictionaryTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $categoryGroupAttributeDictionaryTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_CATEGORY_GROUP_ATTRIBUTE_DICTIONARY));

        $categoryGroupAttributeDictionaryTable
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

        $this->getConnection()->createTable($categoryGroupAttributeDictionaryTable);
    }
}
