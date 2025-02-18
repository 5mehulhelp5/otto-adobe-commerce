<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m06;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Listing as ListingResource;
use Magento\Framework\DB\Ddl\Table;

class AddDescriptionTemplateIdToListing extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING);

        $modifier->addColumn(
            ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID,
            Table::TYPE_INTEGER,
            null,
            ListingResource::COLUMN_STORE_ID,
        );
    }
}
