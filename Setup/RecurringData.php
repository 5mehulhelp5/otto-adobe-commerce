<?php

declare(strict_types=1);

namespace M2E\Otto\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RecurringData implements InstallDataInterface
{
    private const MINIMUM_REQUIRED_MAGENTO_VERSION = '2.4.0';

    private \Magento\Framework\App\ProductMetadataInterface $productMetadata;
    private \M2E\Otto\Helper\Module\Maintenance $maintenance;
    private \M2E\Otto\Setup\InstallHandlerCollection $installHandlerCollection;
    private \M2E\Otto\Setup\InstallTablesListResolver $installTablesListResolver;
    private \M2E\Otto\Setup\UpgradeCollection $upgradeCollection;
    private \M2E\Core\Model\Setup\InstallChecker $installChecker;
    private \M2E\Core\Model\Setup\InstallerFactory $installerFactory;
    private \M2E\Core\Model\Setup\UpgraderFactory $upgraderFactory;
    private \M2E\Otto\Setup\MigrateToCore $migrateToCore;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \M2E\Otto\Helper\Module\Maintenance $maintenance,
        \M2E\Otto\Setup\InstallHandlerCollection $installHandlerCollection,
        \M2E\Otto\Setup\InstallTablesListResolver $installTablesListResolver,
        \M2E\Otto\Setup\UpgradeCollection $upgradeCollection,
        \M2E\Core\Model\Setup\InstallChecker $installChecker,
        \M2E\Core\Model\Setup\InstallerFactory $installerFactory,
        \M2E\Core\Model\Setup\UpgraderFactory $upgraderFactory,
        \M2E\Otto\Setup\MigrateToCore $migrateToCore
    ) {
        $this->productMetadata = $productMetadata;
        $this->maintenance = $maintenance;
        $this->installHandlerCollection = $installHandlerCollection;
        $this->installTablesListResolver = $installTablesListResolver;
        $this->upgradeCollection = $upgradeCollection;
        $this->installChecker = $installChecker;
        $this->installerFactory = $installerFactory;
        $this->upgraderFactory = $upgraderFactory;
        $this->migrateToCore = $migrateToCore;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->checkMagentoVersion($this->productMetadata->getVersion());

        if ($this->migrateToCore->isNeedMigrate()) {
            $this->migrateToCore->migrate($setup->getConnection());
        }

        if (!$this->installChecker->isInstalled(\M2E\Otto\Helper\Module::IDENTIFIER)) {
            $this->installerFactory->create(
                \M2E\Otto\Helper\Module::IDENTIFIER,
                $this->installHandlerCollection,
                $this->installTablesListResolver,
                $setup,
                $this->maintenance
            )->install();

            return;
        }

        $this->upgraderFactory->create(
            \M2E\Otto\Helper\Module::IDENTIFIER,
            $this->upgradeCollection,
            $setup
        )->upgrade();

        $this->maintenance->disable();
    }

    private function checkMagentoVersion(string $magentoVersion): void
    {
        if (!version_compare($magentoVersion, self::MINIMUM_REQUIRED_MAGENTO_VERSION, '>=')) {
            $this->maintenance->enableDueLowMagentoVersion();

            $message = sprintf(
                'Magento version %s is not compatible with M2E Otto Connect version. ' .
                'Please upgrade your Magento first.',
                $magentoVersion,
            );

            throw new \RuntimeException($message);
        }
    }
}
