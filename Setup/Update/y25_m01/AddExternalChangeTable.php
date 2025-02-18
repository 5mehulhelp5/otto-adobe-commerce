<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y25_m01;

use M2E\Otto\Helper\Module\Database\Tables as TablesHelper;
use M2E\Otto\Model\ResourceModel\ExternalChange as ExternalChangeResource;
use M2E\Otto\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DB\Ddl\Table;

class AddExternalChangeTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createExternalChangeTable();
        $this->addStatusChangeDateColumnToProduct();
    }

    private function addStatusChangeDateColumnToProduct(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_STATUS_CHANGE_DATE,
            Table::TYPE_DATETIME,
            null,
            ProductResource::COLUMN_STATUS
        );
    }

    private function createExternalChangeTable(): void
    {
        $externalChangeTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_EXTERNAL_CHANGE));

        $externalChangeTable
            ->addColumn(
                ExternalChangeResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ],
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                ExternalChangeResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('account_id', ExternalChangeResource::COLUMN_ACCOUNT_ID)
            ->addIndex('sku', ExternalChangeResource::COLUMN_SKU)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($externalChangeTable);
    }
}
