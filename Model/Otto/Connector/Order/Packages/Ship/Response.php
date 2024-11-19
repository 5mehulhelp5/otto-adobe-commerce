<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Packages\Ship;

class Response
{
    /** @var \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Error[] */
    private array $errors;

    /**
     * @param \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Error[] $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
