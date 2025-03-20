<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Exception;

class ShippingProfilesUnableProcess extends \M2E\Otto\Model\Exception
{
    /** @var \M2E\Core\Model\Connector\Response\Message[] */
    private $errorMessages;

    public function __construct(
        array $errorMessages,
        string $message = '',
        array $additionalData = [],
        int $code = 0
    ) {
        parent::__construct($message, $additionalData, $code);

        $this->errorMessages = $errorMessages;
    }

    /**
     * @return \M2E\Core\Model\Connector\Response\Message[]
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }
}
