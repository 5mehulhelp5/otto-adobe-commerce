<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\InstallHandler;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use M2E\Otto\Model\ResourceModel\Listing\Log as ListingLogResource;
use M2E\Otto\Model\ResourceModel\Synchronization\Log as SycnLogResource;
use M2E\Otto\Model\ResourceModel\Log\System as LogSystemResource;
use Magento\Framework\DB\Ddl\Table;

class LogHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Otto\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installListingLogTable($setup);
        $this->installOrderLogTable($setup);
        $this->installSynchronizationLogTable($setup);
        $this->installSystemLogTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installListingLogTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_LOG));

        $table
            ->addColumn(
                ListingLogResource::COLUMN_ID,
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
                ListingLogResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_LISTING_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_PRODUCT_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_ACTION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingLogResource::COLUMN_ACTION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                ListingLogResource::COLUMN_INITIATOR,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingLogResource::COLUMN_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                ListingLogResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingLogResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('action', ListingLogResource::COLUMN_ACTION)
            ->addIndex('action_id', ListingLogResource::COLUMN_ACTION_ID)
            ->addIndex('initiator', ListingLogResource::COLUMN_INITIATOR)
            ->addIndex('listing_id', ListingLogResource::COLUMN_LISTING_ID)
            ->addIndex('listing_product_id', ListingLogResource::COLUMN_LISTING_PRODUCT_ID)
            ->addIndex('listing_title', ListingLogResource::COLUMN_LISTING_TITLE)
            ->addIndex('product_id', ListingLogResource::COLUMN_PRODUCT_ID)
            ->addIndex('product_title', ListingLogResource::COLUMN_PRODUCT_TITLE)
            ->addIndex('type', ListingLogResource::COLUMN_TYPE)
            ->addIndex('account_id', ListingLogResource::COLUMN_ACCOUNT_ID)
            ->addIndex('create_date', ListingLogResource::COLUMN_CREATE_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderLogTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $orderLogTable = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_LOG));

        $orderLogTable
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
                'account_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                'type',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 2,
                ]
            )
            ->addColumn(
                'initiator',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 2,
                ]
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT
            )
            ->addColumn(
                'additional_data',
                Table::TYPE_VARBINARY
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME
            )
            ->addIndex('account_id', 'account_id')
            ->addIndex('order_id', 'order_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup
            ->getConnection()
            ->createTable($orderLogTable);
    }

    private function installSynchronizationLogTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_SYNCHRONIZATION_LOG));

        $table
            ->addColumn(
                SycnLogResource::COLUMN_ID,
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
                SycnLogResource::COLUMN_OPERATION_HISTORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SycnLogResource::COLUMN_TASK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SycnLogResource::COLUMN_INITIATOR,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SycnLogResource::COLUMN_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                SycnLogResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                SycnLogResource::COLUMN_DETAILED_DESCRIPTION,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                'additional_data',
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                SycnLogResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('initiator', SycnLogResource::COLUMN_INITIATOR)
            ->addIndex('task', SycnLogResource::COLUMN_TASK)
            ->addIndex('operation_history_id', SycnLogResource::COLUMN_OPERATION_HISTORY_ID)
            ->addIndex('type', SycnLogResource::COLUMN_TYPE)
            ->addIndex('create_date', SycnLogResource::COLUMN_CREATE_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installSystemLogTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_SYSTEM_LOG));

        $table
            ->addColumn(
                LogSystemResource::COLUMN_ID,
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
                LogSystemResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_CLASS,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_DETAILED_DESCRIPTION,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                LogSystemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('type', LogSystemResource::COLUMN_TYPE)
            ->addIndex('class', LogSystemResource::COLUMN_CLASS)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

}
