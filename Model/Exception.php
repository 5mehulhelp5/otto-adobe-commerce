<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

class Exception extends \Exception
{
    private array $additionalData;

    public function __construct(string $message = '', array $additionalData = [], int $code = 0)
    {
        parent::__construct($message, $code, null);

        $this->additionalData = $additionalData;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
