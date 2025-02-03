<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

class Exception extends \Exception
{
    private array $additionalData;

    public function __construct(
        string $message = '',
        array $additionalData = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->additionalData = $additionalData;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
