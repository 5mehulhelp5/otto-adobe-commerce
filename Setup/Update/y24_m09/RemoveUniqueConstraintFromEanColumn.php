<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m09;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Listing\Other as ListingOtherResource;

class RemoveUniqueConstraintFromEanColumn extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING_OTHER);
        $modifier->dropIndex('ean');
        $modifier->addIndex(ListingOtherResource::COLUMN_EAN);

        $this->clearOldSyncLocks();
    }

    private function clearOldSyncLocks(): void
    {
        $tableName = $this->getFullTableName(Tables::TABLE_NAME_LOCK_ITEM);
        $connection = $this->getConnection();

        $selectQuery = $connection->select()
                                  ->from($tableName, ['id'])
                                  ->where("nick LIKE 'synchronization_listing_inventory_for_account_%'")
                                  ->order('create_date DESC')
                                  ->limit(1);
        $newestId = $connection->fetchOne($selectQuery);

        if ($newestId) {
            $deleteQuery = "DELETE FROM {$tableName}
            WHERE nick LIKE 'synchronization_listing_inventory_for_account_%'
            AND id != :newestId";

            $connection->query($deleteQuery, ['newestId' => $newestId]);
        }
    }
}
