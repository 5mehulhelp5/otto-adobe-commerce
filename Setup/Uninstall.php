<?php

namespace M2E\Otto\Setup;

use M2E\Otto\Model\VariablesDir;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{
    private VariablesDir $variablesDir;
    private DeploymentConfig $deploymentConfig;

    private \Magento\Framework\Setup\SchemaSetupInterface $installer;
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(
        VariablesDir $variablesDir,
        DeploymentConfig $deploymentConfig,
        \M2E\Otto\Setup\LoggerFactory $loggerFactory
    ) {
        $this->variablesDir = $variablesDir;
        $this->deploymentConfig = $deploymentConfig;

        $this->logger = $loggerFactory->create();
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->installer = $setup;

        try {
            if (!$this->canRemoveData()) {
                return;
            }

            // Filesystem
            // -----------------------
            $this->variablesDir->removeBase();
            // -----------------------

            // Database
            // -----------------------
            $tables = $setup->getConnection()->getTables(
                $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX)
                . \M2E\Otto\Helper\Module\Database\Tables::PREFIX . '%',
            );

            foreach ($tables as $table) {
                $setup->getConnection()->dropTable($table);
            }

            $setup->getConnection()->delete(
                $setup->getTable('core_config_data'),
                ['path LIKE ?' => 'm2e_otto/%'],
            );
            // -----------------------
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception, 'source' => 'Uninstall']);
        }
    }

    private function canRemoveData(): bool
    {
        $select = $this->installer->getConnection()
                                  ->select()
                                  ->from(
                                      $this->installer->getTable(
                                          \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_CONFIG
                                      ),
                                      'value'
                                  )
                                  ->where('`group` = ?', '/uninstall/')
                                  ->where('`key` = ?', 'can_remove_data');

        return (bool)$this->installer->getConnection()->fetchOne($select);
    }
}
