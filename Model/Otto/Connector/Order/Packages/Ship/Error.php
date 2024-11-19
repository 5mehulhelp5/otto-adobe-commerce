<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Packages\Ship;

class Error
{
    private string $packageId;
    private string $message;

    public function __construct(
        string $packageId,
        string $message
    ) {
        $this->packageId = $packageId;
        $this->message = $message;
    }

    public function getPackageId(): string
    {
        return $this->packageId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
