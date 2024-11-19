<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m09;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Product as ProductResource;

class AddOnlineColumnsToProductTable extends \M2E\Otto\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_CATEGORY_ATTRIBUTES_DATA,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_IMAGES_DATA,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_ONLINE_CATEGORY_ATTRIBUTES_DATA,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_MPN,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_ONLINE_BRAND_NAME,
            false,
            false
        );

        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_MANUFACTURER,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_ONLINE_MPN,
            false,
            false
        );

        $modifier->commit();
    }
}
