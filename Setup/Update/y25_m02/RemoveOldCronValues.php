<?php

declare(strict_types=1);

namespace M2E\Otto\Setup\Update\y25_m02;

class RemoveOldCronValues extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $coreConfig = $this->getConfigModifier(\M2E\Otto\Helper\Module::IDENTIFIER);
        $coreConfig->delete('/cron/', 'last_executed_task_group');
    }
}
