<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Connector\Command;

class ServicingCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    private array $requestData;

    public function __construct(array $requestData)
    {
        $this->requestData = $requestData;
    }

    public function getCommand(): array
    {
        return ['servicing', 'update', 'data'];
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }

    public function parseResponse(
        \M2E\Otto\Model\Connector\Response $response
    ): \M2E\Otto\Model\Connector\Response {
        return $response;
    }
}
