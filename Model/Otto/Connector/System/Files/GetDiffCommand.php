<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\System\Files;

class GetDiffCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    private string $content;
    private string $path;

    public function __construct(string $content, string $path)
    {
        $this->content = $content;
        $this->path = $path;
    }

    public function getCommand(): array
    {
        return ['system', 'files', 'getDiff'];
    }

    public function getRequestData(): array
    {
        return [
            'content' => $this->content,
            'path' => $this->path
        ];
    }

    public function parseResponse(
        \M2E\Otto\Model\Connector\Response $response
    ): \M2E\Otto\Model\Connector\Response {
        return $response;
    }
}
