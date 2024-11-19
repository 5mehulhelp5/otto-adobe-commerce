<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\System\Tables;

class GetDiffCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    public const SEVERITY_CRITICAL = 'critical';
    public const SEVERITY_WARNING = 'warning';

    private string $tablesInfo;

    public function __construct(string $tablesInfo)
    {
        $this->tablesInfo = $tablesInfo;
    }
    public function getCommand(): array
    {
        return ['system', 'tables', 'getDiff'];
    }

    public function getRequestData(): array
    {
        return [
            'tables_info' => $this->tablesInfo
        ];
    }

    public function parseResponse(
        \M2E\Otto\Model\Connector\Response $response
    ): \M2E\Otto\Model\Connector\Response {
        return $response;
    }
}
