<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Brand as BrandResource;
use Magento\Framework\DB\Ddl\Table;

class AddBrandTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $brandTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_BRAND));

        $brandTable
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
                ['nullable' => false]
            )
              ->addColumn(
                  BrandResource::COLUMN_NAME,
                  Table::TYPE_TEXT,
                  255,
                  ['nullable' => false]
              )
              ->addColumn(
                  BrandResource::COLUMN_IS_USABLE,
                  Table::TYPE_BOOLEAN,
                  null,
                  ['nullable' => false]
              )
              ->addIndex('brand_id', 'brand_id')
              ->setOption('type', 'INNODB')
              ->setOption('charset', 'utf8')
              ->setOption('collate', 'utf8_general_ci')
              ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($brandTable);
    }
}
