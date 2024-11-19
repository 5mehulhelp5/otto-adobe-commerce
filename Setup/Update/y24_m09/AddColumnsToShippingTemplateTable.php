<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m09;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Template\Shipping as ShippingResource;

class AddColumnsToShippingTemplateTable extends \M2E\Otto\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_SHIPPING);

        $modifier->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME_MODE,
            'SMALLINT UNSIGNED NOT NULL',
            1,
            ShippingResource::COLUMN_HANDLING_TIME
        );

        $modifier->addColumn(
            ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE,
            'VARCHAR(255)',
            null,
            ShippingResource::COLUMN_HANDLING_TIME_MODE,
        );
    }
}
