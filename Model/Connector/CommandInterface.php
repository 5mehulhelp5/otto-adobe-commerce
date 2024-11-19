<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Connector;

interface CommandInterface
{
    public function getCommand(): array;

    public function getRequestData(): array;

    public function parseResponse(\M2E\Otto\Model\Connector\Response $response): object;
}
