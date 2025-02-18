<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\InstallHandler;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use M2E\Otto\Model\ResourceModel\AttributeMapping\Pair as PairResource;
use Magento\Framework\DB\Ddl\Table;

class MappingHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Otto\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installAttributeMappingTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installAttributeMappingTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ATTRIBUTE_MAPPING));

        $table
            ->addColumn(
                PairResource::COLUMN_ID,
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
                PairResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                PairResource::COLUMN_CHANNEL_ATTRIBUTE_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PairResource::COLUMN_CHANNEL_ATTRIBUTE_CODE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PairResource::COLUMN_MAGENTO_ATTRIBUTE_CODE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PairResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                PairResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('type', PairResource::COLUMN_TYPE)
            ->addIndex('create_date', PairResource::COLUMN_CREATE_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
