<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use Magento\Framework\DB\Ddl\Table;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;

class AddDescriptionColumnsToProductTable extends \M2E\Otto\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ListingProductResource::COLUMN_TEMPLATE_DESCRIPTION_MODE,
            'SMALLINT NOT NULL',
            0,
            ListingProductResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
            false,
            false
        );

        $modifier->addColumn(
            ListingProductResource::COLUMN_TEMPLATE_DESCRIPTION_ID,
            Table::TYPE_INTEGER,
            null,
            ListingProductResource::COLUMN_TEMPLATE_DESCRIPTION_MODE,
            false,
            false
        );

        $modifier->commit();
    }
}
