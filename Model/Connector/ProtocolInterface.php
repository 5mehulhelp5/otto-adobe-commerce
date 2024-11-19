<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Connector;

interface ProtocolInterface
{
    /**
     * @return string
     */
    public function getComponent(): string;

    /**
     * @return int
     */
    public function getComponentVersion(): int;
}
