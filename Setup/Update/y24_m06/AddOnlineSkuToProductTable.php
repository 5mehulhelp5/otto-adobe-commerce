<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use Magento\Framework\DB\Ddl\Table;

class AddOnlineSkuToProductTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{

    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);
        $modifier->addColumn(
            \M2E\Otto\Model\ResourceModel\Product::COLUMN_ONLINE_SKU,
            Table::TYPE_TEXT,
            null,
            \M2E\Otto\Model\ResourceModel\Product::COLUMN_OTTO_PRODUCT_SKU,
        );
    }
}
