<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing;

class ResultHandlerCollection
{
    private const MAP = [
        \M2E\Otto\Model\Listing\InventorySync\Processing\ResultHandler::NICK =>
            \M2E\Otto\Model\Listing\InventorySync\Processing\ResultHandler::class,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Async\Processing\ResultHandler::NICK =>
            \M2E\Otto\Model\Otto\Listing\Product\Action\Async\Processing\ResultHandler::class,
    ];

    public function has(string $nick): bool
    {
        return isset(self::MAP[$nick]);
    }

    /**
     * @param string $nick
     *
     * @return string result handler class name
     */
    public function get(string $nick): string
    {
        return self::MAP[$nick];
    }
}
