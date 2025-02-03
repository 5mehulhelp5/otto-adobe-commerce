<?php

namespace M2E\Otto\Model\Setup;

use Magento\Framework\Setup\SetupInterface;

class Upgrader
{
    /**
     * Means that version, upgrade files are included to the build
     */
    public const MIN_SUPPORTED_VERSION_FOR_UPGRADE = '1.0.0';

    /**
     * @format
     * [
     *     'from_version1' => [
     *         'to' => '$version$',
     *         'upgrade' => null or UpgradeConfig class name,
     *     ],
     *     ...
     * ]
     * @var array
     */
    public static array $availableVersionUpgrades = [
        '1.0.0' => ['to' => '1.0.1', 'upgrade' => null],
        '1.0.1' => ['to' => '1.0.2', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_0_2\Config::class],
        '1.0.2' => ['to' => '1.0.3', 'upgrade' => null],
        '1.0.3' => ['to' => '1.1.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_1_0\Config::class],
        '1.1.0' => ['to' => '1.1.1', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_1_1\Config::class],
        '1.1.1' => ['to' => '1.2.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_2_0\Config::class],
        '1.2.0' => ['to' => '1.2.1', 'upgrade' => null],
        '1.2.1' => ['to' => '1.3.0', 'upgrade' => null],
        '1.3.0' => ['to' => '1.3.1', 'upgrade' => null],
        '1.3.1' => ['to' => '1.4.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_4_0\Config::class],
        '1.4.0' => ['to' => '1.4.1', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_4_1\Config::class],
        '1.4.1' => ['to' => '1.5.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_5_0\Config::class],
        '1.5.0' => ['to' => '1.5.1', 'upgrade' => null],
        '1.5.1' => ['to' => '1.5.2', 'upgrade' => null],
        '1.5.2' => ['to' => '1.5.3', 'upgrade' => null],
        '1.5.3' => ['to' => '1.6.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_6_0\Config::class],
        '1.6.0' => ['to' => '1.6.1', 'upgrade' => null],
        '1.6.1' => ['to' => '1.7.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_7_0\Config::class],
        '1.7.0' => ['to' => '1.8.0', 'upgrade' => \M2E\Otto\Setup\Upgrade\v1_8_0\Config::class],
    ];

    private \Psr\Log\LoggerInterface $logger;
    private \M2E\Otto\Helper\Module\Maintenance $maintenance;
    private \Magento\Framework\Module\ModuleListInterface $moduleList;
    private \M2E\Otto\Model\Setup\Upgrade\ManagerFactory $managerFactory;
    /** @var \M2E\Otto\Model\Setup\Repository */
    private Repository $setupRepository;

    public function __construct(
        \M2E\Otto\Model\Setup\Upgrade\ManagerFactory $managerFactory,
        \M2E\Otto\Helper\Module\Maintenance $maintenance,
        \M2E\Otto\Setup\LoggerFactory $loggerFactory,
        \M2E\Otto\Model\Setup\Repository $setupRepository,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->maintenance = $maintenance;
        $this->moduleList = $moduleList;
        $this->managerFactory = $managerFactory;
        $this->setupRepository = $setupRepository;

        $this->logger = $loggerFactory->create();
    }

    /**
     * Module versions from setup_module magento table uses only by magento for run install or upgrade files.
     * We do not use these versions in setup & upgrade logic (only set correct values to it, using otto_setup_table).
     * So version, that presented in $context parameter, is not used.
     *
     * @param SetupInterface $setup
     */
    public function upgrade(SetupInterface $setup): void
    {
        $setup->startSetup();
        $this->maintenance->enable();

        try {
            foreach ($this->getVersionsToExecute() as $versionFrom => $upgradeData) {
                $setupObject = $this->setupRepository->create($versionFrom, $upgradeData['to']);

                if (isset($upgradeData['upgrade'])) {
                    $upgradeManager = $this->managerFactory->create($upgradeData['upgrade']);
                    $upgradeManager->process();
                }

                $setupObject->markAsCompleted();

                $this->setupRepository->save($setupObject);
            }
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception, 'source' => 'Upgrade']);

            if (isset($setupObject)) {
                $setupObject->setProfilerData($exception->__toString());

                $this->setupRepository->save($setupObject);
            }

            $setup->endSetup();

            return;
        }

        $this->maintenance->disable();
        $setup->endSetup();
    }

    private function getVersionsToExecute(): array
    {
        $versionFrom = $this->getLastInstalledVersion();

        $notCompletedUpgrades = $this->setupRepository->findNotCompletedUpgrades();
        if (!empty($notCompletedUpgrades)) {
            /**
             * Only one not completed upgrade is supported
             */
            $notCompletedUpgrade = reset($notCompletedUpgrades);
            if (version_compare($notCompletedUpgrade->getVersionFrom(), $versionFrom, '<')) {
                $versionFrom = $notCompletedUpgrade->getVersionFrom();
            }
        }

        if (version_compare($versionFrom, self::MIN_SUPPORTED_VERSION_FOR_UPGRADE, '<')) {
            throw new \LogicException(sprintf('This version [%s] is too old.', $versionFrom));
        }

        $versions = [];
        $currentVersion = $this->getConfigVersion();
        while ($versionFrom !== $currentVersion) {
            $upgradeData = self::$availableVersionUpgrades[$versionFrom] ?? null;
            if ($upgradeData === null) {
                break;
            }

            $versions[$versionFrom] = [
                'to' => $upgradeData['to'],
                'upgrade' => $upgradeData['upgrade'] ?? null,
            ];

            $versionFrom = $upgradeData['to'];
        }

        return $versions;
    }

    /**
     * @return string
     */
    private function getConfigVersion(): string
    {
        return $this->moduleList->getOne(\M2E\Otto\Helper\Module::IDENTIFIER)['setup_version'];
    }

    private function getLastInstalledVersion(): string
    {
        $maxCompletedItem = $this->setupRepository->findLastExecuted();
        if ($maxCompletedItem === null) {
            return self::MIN_SUPPORTED_VERSION_FOR_UPGRADE;
        }

        return $maxCompletedItem->getVersionTo();
    }
}
