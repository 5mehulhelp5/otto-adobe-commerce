<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use Magento\Framework\DB\Ddl\Table;
use M2E\Otto\Model\ResourceModel\Product as ListingProductResource;

class AddShippingColumnsToProductTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            'template_shipping_mode',
            'SMALLINT NOT NULL',
            0,
            'template_description_id',
            false,
            false
        );

        $modifier->addColumn(
            'template_shipping_id',
            Table::TYPE_INTEGER,
            null,
            'template_shipping_mode',
            false,
            false
        );

        $modifier->commit();
    }
}
