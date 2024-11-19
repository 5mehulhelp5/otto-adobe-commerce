<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Packages\Ship;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    /**
     * @param \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Package[] $packages
     *
     * @throws \M2E\Otto\Model\Exception
     * @throws \M2E\Otto\Model\Exception\Connection
     */
    public function process(
        \M2E\Otto\Model\Account $account,
        array $packages
    ): \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Response {
        $command = new \M2E\Otto\Model\Otto\Connector\Order\Packages\ShipCommand(
            $account,
            $packages
        );

        /** @var \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Response */
        return $this->singleClient->process($command);
    }
}
