<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\System\Files;

class GetInfoCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    public function getCommand(): array
    {
        return ['system', 'files', 'getInfo'];
    }

    public function getRequestData(): array
    {
        return [];
    }

    public function parseResponse(\M2E\Otto\Model\Connector\Response $response): GetInfo\Response
    {
        $preparedData = [];

        foreach ($response->getResponseData()['files_info'] ?? [] as $info) {
            $preparedData[$info['path']] = $info['hash'];
        }

        return new \M2E\Otto\Model\Otto\Connector\System\Files\GetInfo\Response($preparedData);
    }
}
