<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Module;

class Maintenance
{
    public const MENU_ROOT_NODE_NICK = 'M2E_Otto::otto_maintenance';

    public const MAINTENANCE_CONFIG_PATH = 'm2e_otto/maintenance';

    private const VALUE_DISABLED = 0;
    private const VALUE_ENABLED = 1;
    private const VALUE_ENABLED_DUE_LOW_MAGENTO_VERSION = 2;

    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private \M2E\Otto\Helper\Module\Database\Structure $databaseHelper;

    private array $cache = [];

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Otto\Helper\Module\Database\Structure $databaseHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->databaseHelper = $databaseHelper;
    }

    public function enable(): void
    {
        $this->setConfig(self::MAINTENANCE_CONFIG_PATH, self::VALUE_ENABLED);
    }

    public function isEnabled(): bool
    {
        return (bool)$this->getConfig(self::MAINTENANCE_CONFIG_PATH);
    }

    public function enableDueLowMagentoVersion(): void
    {
        $this->setConfig(self::MAINTENANCE_CONFIG_PATH, self::VALUE_ENABLED_DUE_LOW_MAGENTO_VERSION);
    }

    public function isEnabledDueLowMagentoVersion(): bool
    {
        return (int)$this->getConfig(self::MAINTENANCE_CONFIG_PATH) === self::VALUE_ENABLED_DUE_LOW_MAGENTO_VERSION;
    }

    public function disable(): void
    {
        $this->setConfig(self::MAINTENANCE_CONFIG_PATH, self::VALUE_DISABLED);
    }

    private function getConfig(string $path)
    {
        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        $configDataTableName = $this->databaseHelper
            ->getTableNameWithPrefix('core_config_data');
        $select = $this->resourceConnection->getConnection()
                                           ->select()
                                           ->from($configDataTableName, 'value')
                                           ->where('scope = ?', 'default')
                                           ->where('scope_id = ?', 0)
                                           ->where('path = ?', $path);

        return $this->cache[$path] = $this->resourceConnection->getConnection()->fetchOne($select);
    }

    private function setConfig(string $path, $value): void
    {
        $connection = $this->resourceConnection->getConnection();

        $configDataTableName = $this->databaseHelper
            ->getTableNameWithPrefix('core_config_data');
        if ($this->getConfig($path) === false) {
            $connection->insert(
                $configDataTableName,
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => $path,
                    'value' => $value,
                ]
            );
        } else {
            $connection->update(
                $configDataTableName,
                ['value' => $value],
                [
                    'scope = ?' => 'default',
                    'scope_id = ?' => 0,
                    'path = ?' => $path,
                ]
            );
        }

        unset($this->cache[$path]);
    }
}
