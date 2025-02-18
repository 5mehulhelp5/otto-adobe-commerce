<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ControlPanel\Widget;

class CronInfo implements \M2E\Core\Model\ControlPanel\Widget\CronInfoInterface
{
    private \M2E\Otto\Helper\Module\Cron $cronHelper;
    private \M2E\Otto\Model\Config\Manager $config;

    public function __construct(
        \M2E\Otto\Helper\Module\Cron $cronHelper,
        \M2E\Otto\Model\Config\Manager $config
    ) {
        $this->cronHelper = $cronHelper;
        $this->config = $config;
    }

    public function isMagentoCronDisabled(): bool
    {
        return (bool)(int)$this->config->getGroupValue('/cron/magento/', 'disabled');
    }

    public function isCronWorking(): bool
    {
        return $this->cronHelper->isLastRunMoreThan(1, true);
    }

    public function getCronLastRunTime(): ?\DateTimeInterface
    {
        return $this->cronHelper->getLastRun();
    }

    public function isRunnerTypeMagento(): bool
    {
        return true;
    }

    public function isRunnerTypeDeveloper(): bool
    {
        return false;
    }

    public function isRunnerTypeServiceController(): bool
    {
        return false;
    }

    public function isRunnerTypeServicePub(): bool
    {
        return false;
    }

    public function isControllerCronDisabled(): bool
    {
        return false;
    }

    public function isServicePubDisabled(): bool
    {
        return false;
    }

    public function getServiceAuthKey(): string
    {
        return '';
    }
}
