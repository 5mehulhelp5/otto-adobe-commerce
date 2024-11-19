<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Connector\Client;

class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    public function doError($string): void
    {
        throw new \M2E\Otto\Model\Exception\Connection(
            (string)__(
                'M2E Otto Server connection failed. Find the solution <a target="_blank" href="%url">here</a>',
                ['url' => 'https://help.m2epro.com/support/solutions/articles/9000200887'],
            ),
            [],
            [
                'curl_error_number' => curl_errno($this->_ch),
                'curl_error_message' => $string,
                'curl_info' => curl_getinfo($this->_ch),
            ],
        );
    }
}
