<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

class Module
{
    public const IDENTIFIER = 'M2E_Otto';

    private \Magento\Framework\Module\PackageInfo $packageInfo;
    private \Magento\Framework\Module\ModuleListInterface $moduleList;
    private \Magento\Framework\Module\ModuleResource $moduleResource;
    private \M2E\Otto\Model\Registry\Manager $registry;

    public function __construct(
        \Magento\Framework\Module\PackageInfo $packageInfo,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\ModuleResource $moduleResource,
        \M2E\Otto\Model\Registry\Manager $registry
    ) {
        $this->registry = $registry;
        $this->moduleResource = $moduleResource;
        $this->moduleList = $moduleList;
        $this->packageInfo = $packageInfo;
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Otto-m2';
    }

    /**
     * @return string
     */
    public function getPublicVersion(): string
    {
        return $this->packageInfo->getVersion(self::IDENTIFIER);
    }

    /**
     * @return mixed
     */
    public function getSetupVersion()
    {
        return $this->moduleList->getOne(self::IDENTIFIER)['setup_version'];
    }

    /**
     * @return false|mixed|string
     */
    public function getSchemaVersion()
    {
        return $this->moduleResource->getDbVersion(self::IDENTIFIER);
    }

    /**
     * @return false|mixed|string
     */
    public function getDataVersion()
    {
        return $this->moduleResource->getDataVersion(self::IDENTIFIER);
    }

    public function hasLatestVersion(): bool
    {
        return (bool)$this->getLatestVersion();
    }

    public function setLatestVersion(string $version): void
    {
        $this->registry->setValue(
            '/module/latest_version/',
            $version
        );
    }

    public function getLatestVersion(): ?string
    {
        return $this->registry->getValue('/module/latest_version/');
    }
}
