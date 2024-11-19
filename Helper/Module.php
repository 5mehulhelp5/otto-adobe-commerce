<?php

namespace M2E\Otto\Helper;

use Magento\Framework\Component\ComponentRegistrar;

class Module
{
    public const IDENTIFIER = 'M2E_Otto';

    public const MESSAGE_TYPE_NOTICE = 0;
    public const MESSAGE_TYPE_ERROR = 1;
    public const MESSAGE_TYPE_WARNING = 2;
    public const MESSAGE_TYPE_SUCCESS = 3;

    public const ENVIRONMENT_PRODUCTION = 'production';
    public const ENVIRONMENT_DEVELOPMENT = 'development';

    protected \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory;

    protected \M2E\Otto\Model\Config\Manager $config;

    protected \M2E\Otto\Model\Registry\Manager $registry;

    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    protected \Magento\Framework\Component\ComponentRegistrar $componentRegistrar;

    protected \Magento\Backend\Model\UrlInterface $urlBuilder;

    protected \M2E\Otto\Helper\View\Otto $viewHelper;

    protected ?bool $areImportantTablesExist = null;

    private \M2E\Otto\Helper\Module\Database\Structure $databaseHelper;

    private \M2E\Otto\Helper\Data\Cache\Runtime $runtimeCache;

    private \M2E\Otto\Helper\Data\Cache\Permanent $permanentCache;

    private \M2E\Otto\Helper\Magento $magentoHelper;
    /**
     * @var \M2E\Otto\Helper\Client
     */
    private $clientHelper;

    public function __construct(
        \M2E\Otto\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Otto\Model\Config\Manager $config,
        \M2E\Otto\Model\Registry\Manager $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\Otto\Helper\View\Otto $viewHelper,
        \M2E\Otto\Helper\Module\Database\Structure $databaseHelper,
        \M2E\Otto\Helper\Data\Cache\Runtime $runtimeCache,
        \M2E\Otto\Helper\Data\Cache\Permanent $permanentCache,
        \M2E\Otto\Helper\Magento $magentoHelper,
        \M2E\Otto\Helper\Client $clientHelper
    ) {
        $this->activeRecordFactory = $activeRecordFactory;
        $this->config = $config;
        $this->registry = $registry;
        $this->resourceConnection = $resourceConnection;
        $this->componentRegistrar = $componentRegistrar;
        $this->urlBuilder = $urlBuilder;
        $this->viewHelper = $viewHelper;
        $this->databaseHelper = $databaseHelper;
        $this->runtimeCache = $runtimeCache;
        $this->permanentCache = $permanentCache;
        $this->magentoHelper = $magentoHelper;
        $this->clientHelper = $clientHelper;
    }

    // ----------------------------------------

    /**
     * @return \M2E\Otto\Model\Config\Manager
     * @deprecated use explicitly
     */
    public function getConfig(): \M2E\Otto\Model\Config\Manager
    {
        return $this->config;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return (bool)$this->config->getGroupValue('/', 'is_disabled');
    }

    public function isReadyToWork(): bool
    {
        return $this->areImportantTablesExist()
            && $this->viewHelper->isInstallationWizardFinished();
    }

    /**
     * @return bool
     */
    public function areImportantTablesExist(): bool
    {
        if ($this->areImportantTablesExist !== null) {
            return $this->areImportantTablesExist;
        }

        foreach (['m2e_otto_config', 'm2e_otto_setup'] as $table) {
            $tableName = $this->databaseHelper->getTableNameWithPrefix($table);
            if (!$this->resourceConnection->getConnection()->isTableExists($tableName)) {
                return $this->areImportantTablesExist = false;
            }
        }

        return $this->areImportantTablesExist = true;
    }

    /**
     * @return mixed|null
     */
    public function getEnvironment()
    {
        return $this->config->getGroupValue('/', 'environment');
    }

    /**
     * @return bool
     */
    public function isProductionEnvironment(): bool
    {
        return $this->getEnvironment() === null
            || $this->getEnvironment() === self::ENVIRONMENT_PRODUCTION;
    }

    /**
     * @return bool
     */
    public function isDevelopmentEnvironment(): bool
    {
        return $this->getEnvironment() === self::ENVIRONMENT_DEVELOPMENT;
    }

    /**
     * @param string $env
     *
     * @return void
     */
    public function setEnvironment(string $env): void
    {
        $this->config->setGroupValue('/', 'environment', $env);
    }

    /**
     * @return bool|mixed
     */
    public function isStaticContentDeployed()
    {
        $staticContentValidationResult = $this->runtimeCache->getValue(__METHOD__);

        if ($staticContentValidationResult !== null) {
            return $staticContentValidationResult;
        }

        $result = true;

        $moduleDir = \M2E\Otto\Helper\Module::IDENTIFIER . DIRECTORY_SEPARATOR;

        if (
            !$this->magentoHelper->isStaticContentExists($moduleDir . 'css') ||
            !$this->magentoHelper->isStaticContentExists($moduleDir . 'fonts') ||
            !$this->magentoHelper->isStaticContentExists($moduleDir . 'images') ||
            !$this->magentoHelper->isStaticContentExists($moduleDir . 'js')
        ) {
            $result = false;
        }

        $this->runtimeCache->setValue(__METHOD__, $result);

        return $result;
    }

    /**
     * @return array
     */
    public function getUpgradeMessages(): array
    {
        $messages = $this->registry->getValueFromJson('/upgrade/messages/');

        $messages = array_filter($messages, [$this, 'getMessagesFilterModuleMessages']);

        foreach ($messages as &$message) {
            preg_match_all('/%[\w\d]+%/', $message['text'], $placeholders);
            $placeholders = array_unique($placeholders[0]);

            foreach ($placeholders as $placeholder) {
                $key = substr(substr($placeholder, 1), 0, -1);
                if (!isset($message[$key])) {
                    continue;
                }

                if (!strripos($placeholder, 'url')) {
                    $message['text'] = str_replace($placeholder, $message[$key], $message['text']);
                    continue;
                }

                $message[$key] = $this->urlBuilder->getUrl(
                    $message[$key],
                    isset($message[$key . '_args']) ? $message[$key . '_args'] : null
                );

                $message['text'] = str_replace($placeholder, $message[$key], $message['text']);
            }
        }
        unset($message);

        return $messages;
    }

    /**
     * @param array $message
     *
     * @return bool
     */
    public function getMessagesFilterModuleMessages($message): bool
    {
        return isset($message['text'], $message['type']);
    }

    /**
     * @return array|mixed|string|string[]|null
     */
    public function getBaseRelativeDirectory()
    {
        return str_replace(
            $this->clientHelper->getBaseDirectory(),
            '',
            $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::IDENTIFIER)
        );
    }

    /**
     * @return void
     */
    public function clearCache(): void
    {
        $this->permanentCache->removeAllValues();
    }
}
