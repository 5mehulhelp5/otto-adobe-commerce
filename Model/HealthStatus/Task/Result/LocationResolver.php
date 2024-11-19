<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus\Task\Result;

use M2E\Otto\Model\HealthStatus\Task;

class LocationResolver extends \M2E\Otto\Model\AbstractModel
{
    private const KEY_TAB = 'tab';
    private const KEY_FIELD_SET = 'field_set';
    private const KEY_FIELD = 'field';

    public function resolveTabName(Task\AbstractModel $task): string
    {
        $result = $this->usingMap($task);
        if ($result === null) {
            $className = get_class($task);
            throw new \LogicException("Unable to create Result object for task [{$className}]");
        }

        return $result[self::KEY_TAB];
    }

    public function resolveFieldSetName(Task\AbstractModel $task): string
    {
        $result = $this->usingMap($task);
        if ($result === null) {
            $className = get_class($task);
            throw new \LogicException("Unable to create Result object for task [{$className}]");
        }

        return $result[self::KEY_FIELD_SET];
    }

    public function resolveFieldName(Task\AbstractModel $task): string
    {
        $result = $this->usingMap($task);
        if ($result === null) {
            $className = get_class($task);
            throw new \LogicException("Unable to create Result object for task [{$className}]");
        }

        return $result[self::KEY_FIELD];
    }

    private function usingMap(Task\AbstractModel $task): ?array
    {
        $key = \M2E\Otto\Helper\Client::getClassName($task);

        return $this->getMap()[$key] ?? null;
    }

    private function getMap(): array
    {
        return [
            \M2E\Otto\Model\HealthStatus\Task\Database\MysqlInfo\CrashedTables::class => [
                self::KEY_TAB => 'Problems',
                self::KEY_FIELD_SET => 'Database',
                self::KEY_FIELD => 'Crashed Tables',
            ],
            \M2E\Otto\Model\HealthStatus\Task\Database\MysqlInfo\TablesStructure::class => [
                self::KEY_TAB => 'Problems',
                self::KEY_FIELD_SET => 'Database',
                self::KEY_FIELD => 'Scheme (tables, columns)',
            ],
            \M2E\Otto\Model\HealthStatus\Task\Server\Status\SystemLogs::class => [
                self::KEY_TAB => 'Problems',
                self::KEY_FIELD_SET => 'Server',
                self::KEY_FIELD => 'System Log',
            ],
            \M2E\Otto\Model\HealthStatus\Task\Orders\MagentoCreationFailed::class => [
                self::KEY_TAB => 'Problems',
                self::KEY_FIELD_SET => 'Orders',
                self::KEY_FIELD => 'Magento Order Creation',
            ],
        ];
    }
}
