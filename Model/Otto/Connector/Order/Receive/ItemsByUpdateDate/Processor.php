<?php

namespace M2E\Otto\Model\Otto\Connector\Order\Receive\ItemsByUpdateDate;

class Processor
{
    private \M2E\Otto\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\Otto\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    public function process(
        \M2E\Otto\Model\Account $account,
        \DateTimeInterface $updateFrom,
        \DateTimeInterface $updateTo
    ): \M2E\Otto\Model\Otto\Connector\Order\Receive\Response {
        $command = new \M2E\Otto\Model\Otto\Connector\Order\Receive\ItemsByUpdateDateCommand(
            $account->getServerHash(),
            $updateFrom,
            $updateTo,
        );

        /** @var \M2E\Otto\Model\Otto\Connector\Order\Receive\Response */
        return $this->singleClient->process($command);
    }
}
