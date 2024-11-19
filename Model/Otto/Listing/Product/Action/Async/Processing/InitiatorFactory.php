<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Async\Processing;

class InitiatorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Otto\Model\Connector\CommandProcessingInterface $command,
        Params $params
    ): Initiator {
        return $this->objectManager->create(Initiator::class, ['command' => $command, 'params' => $params]);
    }
}
