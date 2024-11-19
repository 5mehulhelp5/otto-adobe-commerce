<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Template\Shipping as ShippingResource;
use Magento\Framework\DB\Ddl\Table;

class AddShippingTemplateTable extends \M2E\Otto\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $templateShippingTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_TEMPLATE_SHIPPING));

        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ],
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_IS_CUSTOM_TEMPLATE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0]
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_TYPE,
            Table::TYPE_TEXT,
            255,
            ['unsigned' => true, 'nullable' => false],
        );

        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_UPDATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null],
        );
        $templateShippingTable->addColumn(
            ShippingResource::COLUMN_CREATE_DATE,
            Table::TYPE_DATETIME,
            null,
            ['default' => null],
        );
        $templateShippingTable->addIndex('title', ShippingResource::COLUMN_TITLE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($templateShippingTable);
    }

}
