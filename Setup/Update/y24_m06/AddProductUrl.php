<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;

class AddProductUrl extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
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
            \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_OTTO_PRODUCT_URL,
            'TEXT',
            'NULL',
            \M2E\Otto\Model\ResourceModel\Listing\Other::COLUMN_DELIVERY,
        );
    }

    private function addColumnToProduct(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);
        $modifier->addColumn(
            \M2E\Otto\Model\ResourceModel\Product::COLUMN_OTTO_PRODUCT_URL,
            'TEXT',
            'NULL',
            \M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_DELIVERY_DATA,
        );
    }
}
