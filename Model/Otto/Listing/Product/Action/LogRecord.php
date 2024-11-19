<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

class LogRecord
{
    private string $message;
    private string $severity;

    public function __construct(
        string $message,
        string $severity
    ) {
        $this->message = $message;
        $this->severity = $severity;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }
}
