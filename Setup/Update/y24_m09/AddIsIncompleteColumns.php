<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m09;

use Magento\Framework\DB\Ddl\Table;
use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Listing\Other as ListingOtherResource;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;

class AddIsIncompleteColumns extends \M2E\Otto\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING_OTHER);

        $modifier->addColumn(
            ListingOtherResource::COLUMN_IS_INCOMPLETE,
            Table::TYPE_BOOLEAN,
            0,
            ListingOtherResource::COLUMN_STATUS,
            false,
            false
        );

        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ListingProductResource::COLUMN_IS_INCOMPLETE,
            Table::TYPE_BOOLEAN,
            0,
            ListingProductResource::COLUMN_STATUS,
            false,
            false
        );

        $modifier->commit();
    }
}
