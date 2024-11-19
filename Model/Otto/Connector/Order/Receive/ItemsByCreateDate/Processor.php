<?php

namespace M2E\Otto\Model\Otto\Connector\Order\Receive\ItemsByCreateDate;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    public function process(
        \M2E\Otto\Model\Account $account,
        \DateTimeInterface $createFrom,
        \DateTimeInterface $createTo
    ): \M2E\Otto\Model\Otto\Connector\Order\Receive\Response {
        $command = new \M2E\Otto\Model\Otto\Connector\Order\Receive\ItemsByCreateDateCommand(
            $account->getServerHash(),
            $createFrom,
            $createTo,
        );

        /** @var \M2E\Otto\Model\Otto\Connector\Order\Receive\Response */
        return $this->singleClient->process($command);
    }
}
