<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m07;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Listing\Other as ListingOtherResource;
use M2E\Otto\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DB\Ddl\Table;

class AddMoinColumnsToProductTable extends \M2E\Otto\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->addColumnToOther();
        $this->addColumnToProduct();
    }

    private function addColumnToOther(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING_OTHER);
        $modifier->addColumn(
            ListingOtherResource::COLUMN_MOIN,
            'VARCHAR(50)',
            'NULL',
            ListingOtherResource::COLUMN_EAN,
            true
        );
    }

    private function addColumnToProduct(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);
        $modifier->addColumn(
            ProductResource::COLUMN_PRODUCT_MOIN,
            'VARCHAR(50)',
            'NULL',
            ProductResource::COLUMN_ONLINE_EAN,
            true
        );
    }
}
