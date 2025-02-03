<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update;

class Config implements \M2E\Otto\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            'y25_m05' => [
                \M2E\Otto\Setup\Update\y24_m05\AddStatusChangerColumnToScheduledAction::class,
            ],
            'y24_m06' => [
                \M2E\Otto\Setup\Update\y24_m06\AddDescriptionColumnsToProductTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddDescriptionTemplateIdToListing::class,
                \M2E\Otto\Setup\Update\y24_m06\AddDescriptionTemplateTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddCategoryGroupDictionaryTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddCategoryDictionaryTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddAttributeDictionaryTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddCategoryTable::class,
                \M2E\Otto\Setup\Update\y24_m06\ListingWizard::class,
                \M2E\Otto\Setup\Update\y24_m06\AddProductUrl::class,
                \M2E\Otto\Setup\Update\y24_m06\AddMagentoShipmentIdColumnToOrderChange::class,
                \M2E\Otto\Setup\Update\y24_m06\RemoveListingProductAddIds::class,
                \M2E\Otto\Setup\Update\y24_m06\AddAttributeTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddBrandTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddShippingColumnsToProductTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddShippingTemplateIdToListing::class,
                \M2E\Otto\Setup\Update\y24_m06\AddShippingTemplateTable::class,
                \M2E\Otto\Setup\Update\y24_m06\AddBulletPointsToDescPolicy::class,
                \M2E\Otto\Setup\Update\y24_m06\AddOnlineSkuToProductTable::class,
                \M2E\Otto\Setup\Update\y24_m06\RemoveListingProductConfigurations::class,
            ],
            'y25_m07' => [
                \M2E\Otto\Setup\Update\y24_m07\AddMoinColumnsToProductTable::class
            ],
            'y25_m08' => [
                \M2E\Otto\Setup\Update\y24_m08\DropImageAndImageRelationTables::class,
                \M2E\Otto\Setup\Update\y24_m08\RefactorCategoryTable::class,
                \M2E\Otto\Setup\Update\y24_m08\UpdateProductStatus::class,
            ],
            'y25_m09' => [
                \M2E\Otto\Setup\Update\y24_m09\AddColumnsToShippingTemplateTable::class,
                \M2E\Otto\Setup\Update\y24_m09\RemoveUniqueConstraintFromEanColumn::class,
                \M2E\Otto\Setup\Update\y24_m09\AddOnlineColumnsToProductTable::class,
                \M2E\Otto\Setup\Update\y24_m09\FixTablesStructure::class,
                \M2E\Otto\Setup\Update\y24_m09\AddIsIncompleteColumns::class,
            ],
            'y25_m11' => [
                \M2E\Otto\Setup\Update\y24_m11\AddShippingProfiles::class,
                \M2E\Otto\Setup\Update\y24_m11\AddAttributeMapping::class,
            ],
            'y25_m01' => [
                \M2E\Otto\Setup\Update\y25_m01\AddTrackDirectDatabaseChanges::class
            ],
        ];
    }
}
