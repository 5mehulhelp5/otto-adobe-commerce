<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Account;

class GetGrantAccessUrlCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    private string $backUrl;
    private string $mode;

    public function __construct(string $backUrl, string $mode)
    {
        $this->backUrl = $backUrl;
        $this->mode = $mode;
    }

    public function getCommand(): array
    {
        return ['account', 'get', 'grantAccessUrl'];
    }

    public function getRequestData(): array
    {
        return [
            'back_url' => $this->backUrl,
            'mode' => $this->mode,
        ];
    }

    public function parseResponse(\M2E\Otto\Model\Connector\Response $response): object
    {
        return new GetGrantAccessUrl\Response($response->getResponseData()['url']);
    }
}
