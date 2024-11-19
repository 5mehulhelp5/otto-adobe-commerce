<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\System\Config;

class GetInfoCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    public function getCommand(): array
    {
        return ['system', 'configs', 'getInfo'];
    }

    public function getRequestData(): array
    {
        return [];
    }

    public function parseResponse(
        \M2E\Otto\Model\Connector\Response $response
    ): \M2E\Otto\Model\Connector\Response {
        return $response;
    }
}
