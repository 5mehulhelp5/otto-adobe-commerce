<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing;

interface SimpleInitiatorInterface
{
    public function getInitCommand(): \M2E\Otto\Model\Connector\CommandProcessingInterface;

    public function generateProcessParams(): array;

    /**
     * @return string
     */
    public function getResultHandlerNick(): string;

    public function initLock(LockManager $lockManager): void;
}
