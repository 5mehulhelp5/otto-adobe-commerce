<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Order\Change as OrderChangeResource;

class AddMagentoShipmentIdColumnToOrderChange extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ORDER_CHANGE);
        $modifier->addColumn(
            OrderChangeResource::COLUMN_MAGENTO_SHIPMENT_ID,
            'INT UNSIGNED NOT NULL',
            '0',
            OrderChangeResource::COLUMN_ORDER_ID,
        );
    }
}
