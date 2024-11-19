<?php

declare(strict_types=1);

namespace M2E\Otto\Observer\Shipment;

class EventRuntimeManager
{
    private static bool $isNeedSkipEvents = false;

    public function skipEvents(): void
    {
        self::$isNeedSkipEvents = true;
    }

    public function doNotSkipEvents(): void
    {
        self::$isNeedSkipEvents = false;
    }

    public function isNeedSkipEvents(): bool
    {
        return self::$isNeedSkipEvents;
    }
}
