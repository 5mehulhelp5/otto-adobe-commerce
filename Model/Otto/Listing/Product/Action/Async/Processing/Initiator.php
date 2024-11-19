<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Async\Processing;

class Initiator implements \M2E\Otto\Model\Processing\SimpleInitiatorInterface
{
    private \M2E\Otto\Model\Connector\CommandProcessingInterface $command;
    private Params $params;

    public function __construct(
        \M2E\Otto\Model\Connector\CommandProcessingInterface $command,
        Params $params
    ) {
        $this->command = $command;
        $this->params = $params;
    }

    public function getInitCommand(): \M2E\Otto\Model\Connector\CommandProcessingInterface
    {
        return $this->command;
    }

    public function generateProcessParams(): array
    {
        return $this->params->toArray();
    }

    public function getResultHandlerNick(): string
    {
        return ResultHandler::NICK;
    }

    public function initLock(\M2E\Otto\Model\Processing\LockManager $lockManager): void
    {
        // Lock will be acquired in the Start action.
    }
}
