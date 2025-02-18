<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\InstallHandler;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use Magento\Framework\DB\Ddl\Table;
use M2E\Otto\Model\ResourceModel\Account as AccountResource;

class AccountHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Otto\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installAccountTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installAccountTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_ACCOUNT));

        $table
            ->addColumn(
                AccountResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                AccountResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_SERVER_HASH,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_INSTALLATION_ID,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_MODE,
                Table::TYPE_TEXT,
                15,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                AccountResource::COLUMN_CREATE_MAGENTO_INVOICE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                AccountResource::COLUMN_CREATE_MAGENTO_SHIPMENT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => '[]']
            )
            ->addColumn(
                AccountResource::COLUMN_OTHER_LISTINGS_RELATED_STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                AccountResource::COLUMN_ORDER_LAST_SYNC,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                AccountResource::COLUMN_INVENTORY_LAST_SYNC,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                AccountResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                AccountResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('title', AccountResource::COLUMN_TITLE)
            ->addIndex('installation_id', AccountResource::COLUMN_INSTALLATION_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
