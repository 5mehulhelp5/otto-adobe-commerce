<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Exception\Connection;

class SystemError extends \M2E\Otto\Model\Exception
{
    private \M2E\Otto\Model\Connector\Response\MessageCollection $messageCollection;

    public function __construct(
        string $message = '',
        \M2E\Otto\Model\Connector\Response\MessageCollection $messageCollection = null,
        array $additionalData = [],
        int $code = 0
    ) {
        parent::__construct($message, $additionalData, $code);

        $this->messageCollection = $messageCollection;
    }

    public function getMessageCollection(): ?\M2E\Otto\Model\Connector\Response\MessageCollection
    {
        return $this->messageCollection;
    }
}
