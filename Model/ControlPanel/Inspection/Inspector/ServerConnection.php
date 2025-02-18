<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ControlPanel\Inspection\Inspector;

use M2E\Otto\Model\Exception\Connection;

class ServerConnection implements \M2E\Core\Model\ControlPanel\Inspection\InspectorInterface
{
    private \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory;
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory,
        \M2E\Otto\Model\Connector\Client\Single $serverClient
    ) {
        $this->issueFactory = $issueFactory;
        $this->serverClient = $serverClient;
    }

    public function process(): array
    {
        $issues = [];

        try {
            $this->serverClient->process(new \M2E\Core\Model\Server\Connector\CheckStateCommand());
        } catch (Connection $exception) {
            $issues[] = $this->issueFactory->create(
                $exception->getMessage(),
                $exception->getCurlInfo()
            );
        }

        return $issues;
    }
}
