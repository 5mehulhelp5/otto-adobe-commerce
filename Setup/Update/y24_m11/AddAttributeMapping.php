<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m11;

use M2E\Otto\Model\ResourceModel\AttributeMapping\Pair as PairResource;

class AddAttributeMapping extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $tableName = $this->getFullTableName(
            \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_ATTRIBUTE_MAPPING
        );
        $newTable = $this->getConnection()->newTable(
            $tableName
        );
        $newTable
            ->addColumn(
                PairResource::COLUMN_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                PairResource::COLUMN_TYPE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                PairResource::COLUMN_CHANNEL_ATTRIBUTE_TITLE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PairResource::COLUMN_CHANNEL_ATTRIBUTE_CODE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PairResource::COLUMN_MAGENTO_ATTRIBUTE_CODE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PairResource::COLUMN_UPDATE_DATE,
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                PairResource::COLUMN_CREATE_DATE,
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('type', PairResource::COLUMN_TYPE)
            ->addIndex('create_date', PairResource::COLUMN_CREATE_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($newTable);
    }
}
