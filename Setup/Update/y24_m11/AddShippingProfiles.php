<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y24_m11;

use M2E\Otto\Helper\Module\Database\Tables;
use M2E\Otto\Model\ResourceModel\Template\Shipping as ShippingResource;
use M2E\Otto\Model\ResourceModel\Product as ProductResource;
use M2E\Otto\Model\ResourceModel\Listing\Other as OtherResource;

class AddShippingProfiles extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_SHIPPING);

        $modifier->addColumn(
            ShippingResource::COLUMN_ACCOUNT_ID,
            'INT UNSIGNED',
            null,
            ShippingResource::COLUMN_ID,
            true,
            false
        );

        $modifier->addColumn(
            ShippingResource::COLUMN_SHIPPING_PROFILE_ID,
            'VARCHAR(255)',
            null,
            ShippingResource::COLUMN_ACCOUNT_ID,
            true,
            false
        );

        $modifier->addColumn(
            ShippingResource::COLUMN_TRANSPORT_TIME,
            'SMALLINT UNSIGNED',
            null,
            ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE,
            false,
            false
        );

        $modifier->addColumn(
            ShippingResource::COLUMN_ORDER_CUTOFF,
            'VARCHAR(255)',
            null,
            ShippingResource::COLUMN_TRANSPORT_TIME,
            false,
            false
        );

        $modifier->addColumn(
            ShippingResource::COLUMN_WORKING_DAYS,
            'VARCHAR(255)',
            '[]',
            ShippingResource::COLUMN_ORDER_CUTOFF,
            false,
            false
        );

        $modifier->addColumn(
            ShippingResource::COLUMN_IS_DELETED,
            'SMALLINT UNSIGNED',
            0,
            ShippingResource::COLUMN_TYPE,
            false,
            false
        );
        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);
        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_SHIPPING_PROFILE_ID,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_ONLINE_DELIVERY_DATA,
            true,
            false
        );
        $modifier->addColumn(
            ProductResource::COLUMN_ONLINE_DELIVERY_TYPE,
            'VARCHAR(255)',
            null,
            ProductResource::COLUMN_ONLINE_SHIPPING_PROFILE_ID,
            false,
            false
        );
        $modifier->commit();

        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING_OTHER);

        $modifier->addColumn(
            OtherResource::COLUMN_SHIPPING_PROFILE_ID,
            'VARCHAR(255)',
            null,
            OtherResource::COLUMN_DELIVERY,
            false,
            false
        );
        $modifier->commit();

        $this->migrateDeliveryType();

        $this->deleteUnusedShippingPolicies();
        $this->handleShippingPolicies();
    }

    private function migrateDeliveryType(): void
    {
        $connection = $this->getConnection();
        $productTable = $this->getFullTableName(Tables::TABLE_NAME_PRODUCT);

        $deliveryData = $connection->fetchAll("
        SELECT DISTINCT " . ProductResource::COLUMN_ONLINE_DELIVERY_DATA . " AS delivery_data
        FROM {$productTable}
        WHERE " . ProductResource::COLUMN_ONLINE_DELIVERY_DATA . " IS NOT NULL
    ");

        foreach ($deliveryData as $data) {
            $jsonDeliveryData = $data['delivery_data'];
            $deliveryDataDecoded = json_decode($data['delivery_data'], true);
            $deliveryType = $deliveryDataDecoded['delivery_type'];

            $connection->update(
                $productTable,
                [ProductResource::COLUMN_ONLINE_DELIVERY_TYPE => $deliveryType],
                [ProductResource::COLUMN_ONLINE_DELIVERY_DATA . ' = ?' => $jsonDeliveryData]
            );
        }
    }

    private function deleteUnusedShippingPolicies(): void
    {
        $connection = $this->getConnection();

        $usedShippingIds = $connection->fetchCol("
        SELECT DISTINCT template_shipping_id
        FROM " . $this->getFullTableName(Tables::TABLE_NAME_LISTING) . "
        WHERE template_shipping_id IS NOT NULL
    ");

        if (empty($usedShippingIds)) {
            $connection->truncateTable($this->getFullTableName(Tables::TABLE_NAME_TEMPLATE_SHIPPING));
            return;
        }

        $connection->delete(
            $this->getFullTableName(Tables::TABLE_NAME_TEMPLATE_SHIPPING),
            ['id NOT IN (?)' => $usedShippingIds]
        );
    }

    private function handleShippingPolicies(): void
    {
        $shippingAccounts = $this->getConnection()->fetchAll("
            SELECT template_shipping_id, account_id
            FROM " . $this->getFullTableName(Tables::TABLE_NAME_LISTING) . "
            WHERE template_shipping_id IS NOT NULL
            GROUP BY template_shipping_id, account_id
        ");

        $shippingMap = [];

        foreach ($shippingAccounts as $data) {
            $templateShippingId = (int)$data['template_shipping_id'];
            $accountId = (int)$data['account_id'];

            if (!isset($shippingMap[$templateShippingId])) {
                $shippingMap[$templateShippingId] = [];
            }

            $shippingMap[$templateShippingId][] = $accountId;
        }

        foreach ($shippingMap as $templateShippingId => $accountIds) {
            if (count($accountIds) === 1) {
                $this->assignShippingPolicyToAccount($templateShippingId, reset($accountIds));
            } else {
                $primaryAccountId = $accountIds[0];

                $this->assignShippingPolicyToAccount($templateShippingId, $primaryAccountId);

                foreach (array_slice($accountIds, 1) as $accountId) {
                    $newShippingId = $this->duplicateShippingPolicy($templateShippingId, $accountId);
                    $this->updateListingsTemplateShippingId($templateShippingId, $newShippingId, $accountId);
                }
            }
        }
    }

    private function duplicateShippingPolicy(int $shippingId, int $accountId): int
    {
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $connection */
        $connection = $this->getConnection();

        $shippingData = $connection->fetchRow(
            $connection->select()
                       ->from($this->getFullTableName(Tables::TABLE_NAME_TEMPLATE_SHIPPING))
                       ->where('id = ?', $shippingId)
        );

        $accountTitle = $connection->fetchOne(
            $connection->select()
                       ->from($this->getFullTableName(Tables::TABLE_NAME_ACCOUNT), \M2E\Otto\Model\ResourceModel\Account::COLUMN_TITLE)
                       ->where('id = ?', $accountId)
        );

        unset($shippingData['id']);
        $shippingData['title'] = $accountTitle;
        $shippingData['account_id'] = $accountId;

        $connection->insert($this->getFullTableName(Tables::TABLE_NAME_TEMPLATE_SHIPPING), $shippingData);

        return (int)$connection->lastInsertId();
    }

    private function assignShippingPolicyToAccount(int $shippingId, int $accountId): void
    {
        $this->getConnection()->update(
            $this->getFullTableName(Tables::TABLE_NAME_TEMPLATE_SHIPPING),
            ['account_id' => $accountId],
            ['id = ?' => $shippingId]
        );
    }

    private function updateListingsTemplateShippingId(int $oldShippingId, int $newShippingId, int $accountId): void
    {
        $this->getConnection()->update(
            $this->getFullTableName(Tables::TABLE_NAME_LISTING),
            ['template_shipping_id' => $newShippingId],
            [
                'template_shipping_id = ?' => $oldShippingId,
                'account_id = ?' => $accountId
            ]
        );
    }
}
