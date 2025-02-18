<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus;

use M2E\Otto\Model\HealthStatus\Task\Result\Set;

class CurrentStatus
{
    private \M2E\Otto\Model\Registry\Manager $registry;

    public function __construct(
        \M2E\Otto\Model\Registry\Manager $registry
    ) {
        $this->registry = $registry;
    }

    public function get(): int
    {
        return (int)$this->registry->getValue('/health_status/current_status/');
    }

    public function set(Set $resultSet): void
    {
        $this->registry->setValue(
            '/health_status/current_status/',
            (string)$resultSet->getWorstState()
        );

        $details = [];
        foreach ($resultSet->getByKeys() as $result) {
            $details[$result->getTaskHash()] = [
                'result' => $result->getTaskResult(),
                'data' => $result->getTaskData(),
            ];
        }

        $this->registry->setValue(
            '/health_status/details/',
            \M2E\Otto\Helper\Json::encode($details)
        );
    }
}
