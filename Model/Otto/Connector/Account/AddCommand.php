<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Account;

class AddCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    private string $authCode;
    private string $title;
    private string $mode;

    public function __construct(string $title, string $authCode, string $mode)
    {
        $this->title = $title;
        $this->authCode = $authCode;
        $this->mode = $mode;
    }

    public function getCommand(): array
    {
        return ['account', 'add', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'title' => $this->title,
            'auth_code' => $this->authCode,
            'mode' => $this->mode,
        ];
    }

    public function parseResponse(\M2E\Otto\Model\Connector\Response $response): Add\Response
    {
        $responseData = $response->getResponseData();

        return new \M2E\Otto\Model\Otto\Connector\Account\Add\Response(
            $responseData['hash'],
            $responseData['account']['installation_id']
        );
    }
}
