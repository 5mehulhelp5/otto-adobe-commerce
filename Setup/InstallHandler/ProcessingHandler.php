<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\InstallHandler;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use Magento\Framework\DB\Ddl\Table;

class ProcessingHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Otto\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installProcessingTable($setup);
        $this->installProcessingPartialDataTable($setup);
        $this->installProcessingLockTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installProcessingTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING));

        $table
            ->addColumn(
                'id',
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
                'type',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'server_hash',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'stage',
                Table::TYPE_TEXT,
                20,
                ['nullable' => false]
            )
            ->addColumn(
                'handler_nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'params',
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'result_data',
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'result_messages',
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'data_next_part',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true]
            )
            ->addColumn(
                'is_completed',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'expiration_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('type', 'type')
            ->addIndex('stage', 'stage')
            ->addIndex('is_completed', 'is_completed')
            ->addIndex('expiration_date', 'expiration_date')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProcessingPartialDataTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING_PARTIAL_DATA));

        $table
            ->addColumn(
                'id',
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
                'processing_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'part_number',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'data',
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addIndex('part_number', 'part_number')
            ->addIndex('processing_id', 'processing_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProcessingLockTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING_LOCK));

        $table
            ->addColumn(
                'id',
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
                'processing_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'object_nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'object_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'tag',
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('processing_id', 'processing_id')
            ->addIndex('object_nick', 'object_nick')
            ->addIndex('object_id', 'object_id')
            ->addIndex('tag', 'tag')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $setup->getConnection()->createTable($table);
    }
}
