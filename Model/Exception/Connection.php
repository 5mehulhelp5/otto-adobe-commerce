<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Exception;

class Connection extends \M2E\Otto\Model\Exception
{
    private array $curlInfo;

    public function __construct(
        string $message,
        array $additionalData = [],
        array $curlInfo = []
    ) {
        parent::__construct($message, $additionalData + ['curl_info' => $curlInfo]);

        $this->curlInfo = $curlInfo;
    }

    public function getCurlInfo(): array
    {
        return $this->curlInfo;
    }
}
