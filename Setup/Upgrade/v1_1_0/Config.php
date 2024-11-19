<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Upgrade\v1_1_0;

class Config implements \M2E\Otto\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Otto\Setup\Update\y24_m05\AddStatusChangerColumnToScheduledAction::class,
            \M2E\Otto\Setup\Update\y24_m06\AddDescriptionColumnsToProductTable::class,
            \M2E\Otto\Setup\Update\y24_m06\AddDescriptionTemplateIdToListing::class,
            \M2E\Otto\Setup\Update\y24_m06\AddDescriptionTemplateTable::class,
            \M2E\Otto\Setup\Update\y24_m06\AddCategoryGroupDictionaryTable::class,
            \M2E\Otto\Setup\Update\y24_m06\AddCategoryDictionaryTable::class,
            \M2E\Otto\Setup\Update\y24_m06\AddAttributeDictionaryTable::class,
            \M2E\Otto\Setup\Update\y24_m06\AddCategoryTable::class,
            \M2E\Otto\Setup\Update\y24_m06\ListingWizard::class,
            \M2E\Otto\Setup\Update\y24_m06\RemoveListingProductAddIds::class,
            \M2E\Otto\Setup\Update\y24_m06\AddAttributeTable::class,
            \M2E\Otto\Setup\Update\y24_m06\AddBrandTable::class,
            \M2E\Otto\Setup\Update\y24_m06\AddShippingColumnsToProductTable::class,
            \M2E\Otto\Setup\Update\y24_m06\AddShippingTemplateIdToListing::class,
            \M2E\Otto\Setup\Update\y24_m06\AddShippingTemplateTable::class,
            \M2E\Otto\Setup\Update\y24_m06\AddBulletPointsToDescPolicy::class,
            \M2E\Otto\Setup\Update\y24_m06\AddOnlineSkuToProductTable::class,
            \M2E\Otto\Setup\Update\y24_m06\RemoveListingProductConfigurations::class,
        ];
    }
}
