<?php

namespace M2E\Otto\Model\Otto\Connector\Order\Receive;

class Response
{
    private array $orders;
    private \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection;
    private \DateTime $toDate;

    public function __construct(
        array $orders,
        \DateTime $toDate,
        \M2E\Core\Model\Connector\Response\MessageCollection $messageCollection
    ) {
        $this->orders = $orders;
        $this->messageCollection = $messageCollection;
        $this->toDate = $toDate;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }

    public function getMessageCollection(): \M2E\Core\Model\Connector\Response\MessageCollection
    {
        return $this->messageCollection;
    }

    public function getToDate(): \DateTime
    {
        return $this->toDate;
    }
}
