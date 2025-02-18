<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Module;

class Maintenance implements \M2E\Core\Model\Module\MaintenanceInterface
{
    public const MENU_ROOT_NODE_NICK = 'M2E_Otto::otto_maintenance';

    private const MAINTENANCE_CONFIG_PATH = 'm2e_otto/maintenance';

    private \M2E\Core\Model\Module\Maintenance\AdapterFactory $adapterFactory;
    private \M2E\Core\Model\Module\Maintenance\Adapter $adapter;

    public function __construct(
        \M2E\Core\Model\Module\Maintenance\AdapterFactory $adapterFactory
    ) {
        $this->adapterFactory = $adapterFactory;
    }

    public function enable(): void
    {
        $this->getAdapter()->enable();
    }

    public function isEnabled(): bool
    {
        return $this->getAdapter()->isEnabled();
    }

    public function enableDueLowMagentoVersion(): void
    {
        $this->getAdapter()->enableDueLowMagentoVersion();
    }

    public function isEnabledDueLowMagentoVersion(): bool
    {
        return $this->getAdapter()->isEnabledDueLowMagentoVersion();
    }

    public function disable(): void
    {
        $this->getAdapter()->disable();
    }

    public function getAdapter(): \M2E\Core\Model\Module\Maintenance\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory
                ->create(self::MAINTENANCE_CONFIG_PATH);
        }

        return $this->adapter;
    }
}
