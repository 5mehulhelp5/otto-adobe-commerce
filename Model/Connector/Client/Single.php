<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Connector\Client;

use M2E\Otto\Model\Connector\RequestBuilder;
use M2E\Otto\Model\Connector\ResponseParser;

class Single
{
    private \M2E\Otto\Model\Connector\Protocol $protocol;
    private Config $config;
    private \M2E\Otto\Helper\Client $clientHelper;
    private RequestBuilder $requestBuilder;
    private Curl $curl;
    private \M2E\Otto\Helper\Module\Exception $exceptionLogger;
    private \M2E\Otto\Helper\Module\Logger $logger;

    public function __construct(
        \M2E\Otto\Model\Connector\Protocol $protocol,
        \M2E\Otto\Model\Connector\Client\Config $config,
        \M2E\Otto\Helper\Client $clientHelper,
        RequestBuilder $requestBuilder,
        Curl $curl,
        \M2E\Otto\Helper\Module\Exception $exceptionLogger,
        \M2E\Otto\Helper\Module\Logger $logger
    ) {
        $this->protocol = $protocol;
        $this->config = $config;
        $this->clientHelper = $clientHelper;
        $this->requestBuilder = $requestBuilder;
        $this->curl = $curl;
        $this->exceptionLogger = $exceptionLogger;
        $this->logger = $logger;
    }

    /**
     * @param \M2E\Otto\Model\Connector\CommandInterface $command
     *
     * @return object
     * @throws \M2E\Otto\Model\Exception
     * @throws \M2E\Otto\Model\Exception\Connection
     */
    public function process(\M2E\Otto\Model\Connector\CommandInterface $command): object
    {
        try {
            $requestTime = \M2E\Otto\Helper\Date::createCurrentGmt();

            $result = $this->sendRequest($command);
        } finally {
            $this->clientHelper->updateMySqlConnection();
        }

        try {
            $response = ResponseParser::parse($result);
            $response->setRequestTime($requestTime);
        } catch (\M2E\Otto\Model\Exception\Connection\InvalidResponse $e) {
            $this->logger->process($result, 'Invalid Response Format');

            $message = (string)__(
                'M2E Otto Server connection failed. Find the solution <a target="_blank" href="%url">here</a>',
                [
                    'url' => 'https://help.m2epro.com/support/solutions/articles/9000200887',
                ],
            );

            throw new \M2E\Otto\Model\Exception\Connection(
                $message,
                ['result' => $result],
            );
        }

        if ($response->getMessageCollection()->hasSystemErrors()) {
            throw new \M2E\Otto\Model\Exception\Connection\SystemError(
                (string)__(
                    'Internal Server Error(s) [%error_message]',
                    ['error_message' => $response->getMessageCollection()->getCombinedSystemErrorsString()],
                ),
                $response->getMessageCollection(),
            );
        }

        return $command->parseResponse(
            $response
        );
    }

    /**
     * @param \M2E\Otto\Model\Connector\CommandInterface $command
     *
     * @return string
     * @throws \M2E\Otto\Model\Exception\Connection
     */
    private function sendRequest(\M2E\Otto\Model\Connector\CommandInterface $command): string
    {
        $this->curl->setTimeout($this->config->getTimeout());
        $this->curl->setOption(CURLOPT_CONNECTTIMEOUT, $this->config->getConnectionTimeout());

        try {
            $this->curl->post(
                $this->config->getHost(),
                $this->requestBuilder->build($command, $this->protocol),
            );
        } catch (\Throwable $e) {
            $this->exceptionLogger->process($e, ['command' => $command->getCommand()]);

            throw $e;
        }

        return $this->curl->getBody();
    }
}
