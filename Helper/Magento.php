<?php

namespace M2E\Otto\Helper;

use Magento\Deploy\Package\Package;

class Magento
{
    public const CLOUD_COMPOSER_KEY = 'magento/magento-cloud-metapackage';
    public const CLOUD_SERVER_KEY = 'MAGENTO_CLOUD_APPLICATION';
    public const APPLICATION_CLOUD_NICK = 'cloud';
    public const APPLICATION_PERSONAL_NICK = 'personal';

    public const ENTERPRISE_EDITION_NICK = 'enterprise';
    public const COMMUNITY_EDITION_NICK = 'community';

    public const MAGENTO_INVENTORY_MODULE_NICK = 'Magento_Inventory';

    private \Magento\Framework\App\View\Deployment\Version\Storage\File $deploymentVersionStorageFile;
    private \Magento\Framework\Filesystem $filesystem;
    private \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver;
    private \Magento\Framework\App\ProductMetadataInterface $productMetadata;
    private \Magento\Framework\App\ResourceConnection $resource;
    private \Magento\Framework\Module\ModuleListInterface $moduleList;
    private \Magento\Framework\App\DeploymentConfig $deploymentConfig;
    private \Magento\Cron\Model\ScheduleFactory $cronScheduleFactory;
    private \Magento\Framework\Locale\ResolverInterface $localeResolver;
    private \Magento\Framework\App\State $appState;
    private \Magento\Framework\Locale\TranslatedLists $translatedLists;
    private \Magento\Directory\Model\CountryFactory $countryFactory;
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \Magento\Framework\App\CacheInterface $appCache;
    private \Magento\Framework\Composer\ComposerInformation $composerInformation;
    private Module\Exception $exceptionHelper;
    private \Magento\Framework\App\RequestInterface $request;
    private \Magento\Framework\UrlInterface $urlBuilder;
    private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper,
        \Magento\Framework\App\View\Deployment\Version\Storage\File $deploymentVersionStorageFile,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Cron\Model\ScheduleFactory $scheduleFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Locale\TranslatedLists $translatedLists,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\CacheInterface $appCache,
        \Magento\Framework\Composer\ComposerInformation $composerInformation
    ) {
        $this->deploymentVersionStorageFile = $deploymentVersionStorageFile;
        $this->filesystem = $filesystem;
        $this->themeResolver = $themeResolver;
        $this->productMetadata = $productMetadata;
        $this->resource = $resource;
        $this->moduleList = $moduleList;
        $this->deploymentConfig = $deploymentConfig;
        $this->cronScheduleFactory = $scheduleFactory;
        $this->localeResolver = $localeResolver;
        $this->appState = $appState;
        $this->translatedLists = $translatedLists;
        $this->countryFactory = $countryFactory;
        $this->objectManager = $objectManager;
        $this->appCache = $appCache;
        $this->composerInformation = $composerInformation;
        $this->exceptionHelper = $exceptionHelper;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param bool $asArray
     *
     * @return string|string[]
     */
    public function getVersion(bool $asArray = false)
    {
        $versionString = $this->productMetadata->getVersion();

        return $asArray ? explode('.', $versionString) : $versionString;
    }

    public function isMSISupportingVersion(): bool
    {
        return $this->moduleList->getOne(self::MAGENTO_INVENTORY_MODULE_NICK) !== null;
    }

    public function isEnterpriseEdition(): bool
    {
        return $this->getEditionName() === self::ENTERPRISE_EDITION_NICK;
    }

    public function getEditionName(): string
    {
        return strtolower($this->productMetadata->getEdition());
    }

    public function isCommunityEdition(): bool
    {
        return $this->getEditionName() === self::COMMUNITY_EDITION_NICK;
    }

    public function getLocation(): string
    {
        return $this->isApplicationCloud() ?
            self::APPLICATION_CLOUD_NICK :
            self::APPLICATION_PERSONAL_NICK;
    }

    public function isApplicationCloud(): bool
    {
        return $this->hasComposerCloudSign() || $this->hasServerCloudSign();
    }

    private function hasComposerCloudSign(): bool
    {
        return $this->composerInformation->isPackageInComposerJson(self::CLOUD_COMPOSER_KEY);
    }

    private function hasServerCloudSign(): bool
    {
        if ($this->request instanceof \Magento\Framework\App\Request\Http) {
            return $this->request->getServer(self::CLOUD_SERVER_KEY) !== null;
        }

        return false;
    }

    public function isDeveloper(): bool
    {
        return $this->appState->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER;
    }

    public function isProduction(): bool
    {
        return $this->appState->getMode() === \Magento\Framework\App\State::MODE_PRODUCTION;
    }

    public function isDefault(): bool
    {
        return $this->appState->getMode() === \Magento\Framework\App\State::MODE_DEFAULT;
    }

    public function isCronWorking(): bool
    {
        $minDateTime = Date::createCurrentGmt();
        $minDateTime->modify('-1 day');
        $minDateTime = $minDateTime->format('Y-m-d H:i:s');

        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
        $collection = $this->cronScheduleFactory->create()->getCollection();
        $collection->addFieldToFilter('executed_at', ['gt' => $minDateTime]);

        return $collection->getSize() > 0;
    }

    public function getBaseUrl(): string
    {
        return str_replace('index.php/', '', $this->urlBuilder->getBaseUrl());
    }

    public function getLocaleCode(): string
    {
        $localeComponents = explode('_', $this->getLocale());

        return strtolower(array_shift($localeComponents));
    }

    public function getLocale(): string
    {
        return $this->localeResolver->getLocale();
    }

    public function getDefaultLocale(): string
    {
        return $this->localeResolver->getDefaultLocale();
    }

    public function getBaseCurrency(): string
    {
        return (string)$this->scopeConfig->getValue(
            \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    //---------------------------------------

    public function getBaseShippingCountry(): string
    {
        $countryCode = (string)$this->scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $country = $this->countryFactory->create()->loadByCode($countryCode);

        return $country->getData('iso3_code');
    }

    public function getBaseShippingZip(): string
    {
        return (string)$this->scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBaseShippingCity(): string
    {
        return (string)$this->scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //---------------------------------------

    public function getCurrentSecretKey(): string
    {
        if (!$this->isSecretKeyToUrl()) {
            return '';
        }

        return $this->urlBuilder->getSecretKey();
    }

    public function isSecretKeyToUrl(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_ADMIN_SECURITY_USEFORMKEY,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    public function isStaticContentExists(string $path): bool
    {
        $directoryReader = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::STATIC_VIEW
        );

        $basePath = $this->getThemePath() . DIRECTORY_SEPARATOR . $this->getLocale() . DIRECTORY_SEPARATOR . $path;
        $exist = $directoryReader->isExist($basePath);

        if (!$exist) {
            $basePath = $this->themeResolver->get()->getArea() . DIRECTORY_SEPARATOR .
                Package::BASE_THEME . DIRECTORY_SEPARATOR . Package::BASE_LOCALE . DIRECTORY_SEPARATOR . $path;

            $exist = $directoryReader->isExist($basePath);
        }

        return $exist;
    }

    public function getThemePath(): string
    {
        return $this->themeResolver->get()->getFullPath();
    }

    public function getLastStaticContentDeployDate()
    {
        try {
            $deployedTimeStamp = $this->deploymentVersionStorageFile->load();
        } catch (\Exception $e) {
            return false;
        }

        return $deployedTimeStamp ? gmdate('Y-m-d H:i:s', $deployedTimeStamp) : false;
    }

    public function getCountries(): array
    {
        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
        $collection = $this->countryFactory->create()->getCollection();

        return $collection->toOptionArray();
    }

    public function getTranslatedCountryName(string $countryId, string $localeCode = 'en_US'): string
    {
        if ($this->localeResolver->getLocale() !== $localeCode) {
            $this->localeResolver->setLocale($localeCode);
        }

        return $this->translatedLists->getCountryTranslation($countryId);
    }

    public function getRegionsByCountryCode(string $countryCode): array
    {
        try {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
        } catch (\Exception $e) {
            $this->exceptionHelper->process($e);

            return [];
        }

        if (!$country->getId()) {
            return [];
        }

        $result = [];
        foreach ($country->getRegions() as $region) {
            /** @var \Magento\Directory\Model\Region $region */
            $result[] = [
                'region_id' => $region->getRegionId(),
                'code' => $region->getCode(),
                'name' => $region->getName(),
            ];
        }

        if (empty($result) && $countryCode === 'AU') {
            $result = [
                ['region_id' => '', 'code' => 'NSW', 'name' => 'New South Wales'],
                ['region_id' => '', 'code' => 'QLD', 'name' => 'Queensland'],
                ['region_id' => '', 'code' => 'SA', 'name' => 'South Australia'],
                ['region_id' => '', 'code' => 'TAS', 'name' => 'Tasmania'],
                ['region_id' => '', 'code' => 'VIC', 'name' => 'Victoria'],
                ['region_id' => '', 'code' => 'WA', 'name' => 'Western Australia'],
            ];
        } elseif (empty($result) && $countryCode === 'GB') {
            $result = [
                ['region_id' => '', 'code' => 'UKH', 'name' => 'East of England'],
                ['region_id' => '', 'code' => 'UKF', 'name' => 'East Midlands'],
                ['region_id' => '', 'code' => 'UKI', 'name' => 'London'],
                ['region_id' => '', 'code' => 'UKC', 'name' => 'North East'],
                ['region_id' => '', 'code' => 'UKD', 'name' => 'North West'],
                ['region_id' => '', 'code' => 'UKJ', 'name' => 'South East'],
                ['region_id' => '', 'code' => 'UKK', 'name' => 'South West'],
                ['region_id' => '', 'code' => 'UKG', 'name' => 'West Midlands'],
                ['region_id' => '', 'code' => 'UKE', 'name' => 'Yorkshire and the Humber'],
            ];
        }

        return $result;
    }

    public function getName(): string
    {
        return 'magento';
    }

    public function getMySqlTables(): array
    {
        return $this->resource->getConnection()->listTables();
    }

    public function getDatabaseName(): string
    {
        return (string)$this->deploymentConfig->get(
            \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
            . '/dbname'
        );
    }

    public function getDatabaseTablesPrefix(): string
    {
        return (string)$this->deploymentConfig->get(
            \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
        );
    }

    public function isInstalled(): bool
    {
        return $this->deploymentConfig->isAvailable();
    }

    public function getModules(): array
    {
        return array_keys((array)$this->deploymentConfig->get('modules'));
    }

    public function getAllEventObservers(): array
    {
        $eventObservers = [];

        /** @var \Magento\Framework\Config\ScopeInterface $scope */
        $scope = $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class);

        foreach ($this->getAreas() as $area) {
            $scope->setCurrentScope($area);

            $eventsData = $this->objectManager->create(
                \Magento\Framework\Event\Config\Data::class,
                ['configScope' => $scope]
            );

            foreach ($eventsData->get(null) as $eventName => $eventData) {
                foreach ($eventData as $observerName => $observerData) {
                    $observerName = '#class#::#method#';

                    if (!empty($observerData['instance'])) {
                        $observerName = str_replace('#class#', $observerData['instance'], $observerName);
                    }

                    $observerMethod = !empty($observerData['method']) ? $observerData['method'] : 'execute';
                    $observerName = str_replace('#method#', $observerMethod, $observerName);
                    $eventObservers[$area][$eventName][] = $observerName;
                }
            }
        }

        return $eventObservers;
    }

    public function getAreas(): array
    {
        return [
            \Magento\Framework\App\Area::AREA_GLOBAL,
            \Magento\Framework\App\Area::AREA_ADMIN,
            \Magento\Framework\App\Area::AREA_FRONTEND,
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            \Magento\Framework\App\Area::AREA_CRONTAB,
        ];
    }

    public function getNextMagentoOrderId(): string
    {
        return '';
    }

    public function clearMenuCache(): void
    {
        $this->appCache->clean([\Magento\Backend\Block\Menu::CACHE_TAGS]);
    }

    public function clearCache(): void
    {
        $this->appCache->clean();
    }
}
