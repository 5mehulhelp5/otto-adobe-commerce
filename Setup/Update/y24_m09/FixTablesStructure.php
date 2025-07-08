<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m09;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Listing as ListingResource;
use M2E\Otto\Model\ResourceModel\Order\Change as OrderChangeResource;
use M2E\Otto\Model\ResourceModel\Product as ProductResource;
use M2E\Otto\Model\ResourceModel\Product\Lock as ProductLockResource;
use Magento\Framework\DB\Ddl\Table;

class FixTablesStructure extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING);

        $modifier->changeColumn(
            ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID,
            'INT UNSIGNED',
            null,
            ListingResource::COLUMN_STORE_ID,
            false
        );
        $modifier->changeColumn(
            ListingResource::COLUMN_TEMPLATE_SHIPPING_ID,
            'INT UNSIGNED',
            null,
            ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
            false
        );
        $modifier->addIndex(ListingResource::COLUMN_TEMPLATE_SHIPPING_ID, false);
        $modifier->addIndex(ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID, false);
        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->changeColumn(
            ProductResource::COLUMN_ONLINE_SKU,
            'VARCHAR(50)',
            null,
            \M2E\Otto\Model\ResourceModel\Product::COLUMN_OTTO_PRODUCT_SKU,
            false
        );
        $modifier->addIndex('template_description_mode', false);
        $modifier->addIndex('template_description_id', false);
        $modifier->addIndex('template_shipping_mode', false);
        $modifier->addIndex('template_shipping_id', false);
        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT_LOCK);

        $modifier->changeColumn(
            ProductLockResource::COLUMN_PRODUCT_ID,
            'INT NOT NULL',
            null,
            ProductLockResource::COLUMN_ID,
            false
        );
        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER_CHANGE);
        $modifier->changeColumn(
            OrderChangeResource::COLUMN_MAGENTO_SHIPMENT_ID,
            'INT UNSIGNED NOT NULL',
            '0',
            OrderChangeResource::COLUMN_ORDER_ID,
            false
        );
        $modifier->commit();
    }
}
