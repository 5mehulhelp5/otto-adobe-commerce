<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y25_m04;

use M2E\Core\Helper\Module\Database\Tables as CoreTables;

class MigrateAttributeMappingToCore extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $connection = $this->getConnection();
        $oldTable = $this->getFullTableName('m2e_otto_attribute_mapping');
        $newTable = $this->getFullTableName(CoreTables::TABLE_NAME_ATTRIBUTE_MAPPING);

        if (!$connection->isTableExists($oldTable)) {
            return;
        }

        $query = $connection->select()->from($oldTable)->query();

        while ($row = $query->fetch()) {
            $exists = $connection->fetchOne(
                $connection->select()
                           ->from($newTable, ['channel_attribute_code'])
                           ->where('`channel_attribute_code` = ?', $row['channel_attribute_code'])
                           ->where('`extension_name` = ?', \M2E\Otto\Helper\Module::IDENTIFIER)

            );

            if (!$exists) {
                $connection->insert($newTable, [
                    'extension_name' => \M2E\Otto\Helper\Module::IDENTIFIER,
                    'type' => $row['type'],
                    'channel_attribute_title' => $row['channel_attribute_title'],
                    'channel_attribute_code' => $row['channel_attribute_code'],
                    'magento_attribute_code' => $row['magento_attribute_code'],
                    'update_date' => $row['update_date'],
                    'create_date' => $row['create_date'],
                ]);
            }
        }

        $this->getConnection()->dropTable($oldTable);
    }
}
