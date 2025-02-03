<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Delete;

use M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidator;

class Processor extends \M2E\Otto\Model\Otto\Listing\Product\Action\AbstractSyncProcessor
{
    private \M2E\Otto\Model\Connector\Client\Single $serverClient;
    private ValidatorFactory $actionValidatorFactory;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidator $actionValidator;

    public function __construct(
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        \M2E\Otto\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
        $this->actionValidatorFactory = $actionValidatorFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
    }

    protected function getActionValidator(): AbstractValidator
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->actionValidator)) {
            return $this->actionValidator;
        }

        return $this->actionValidator = $this->actionValidatorFactory->create(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->getParams()
        );
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Connection
     * @throws \M2E\Otto\Model\Exception
     */
    protected function makeCall(): \M2E\Otto\Model\Connector\Response
    {
        $request = $this->requestFactory->create();

        $requestData = $request->build(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->getLogBuffer(),
            $this->getParams()
        );

        $command = new \M2E\Otto\Model\Otto\Connector\Item\DeleteCommand(
            $this->getAccount()->getServerHash(),
            $requestData->getData(),
        );

        /** @var \M2E\Otto\Model\Connector\Response */
        return $this->serverClient->process($command);
    }

    protected function processSuccess(\M2E\Otto\Model\Connector\Response $response): string
    {
        /** @var Response $responseObj */
        $responseObj = $this->responseFactory->create(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->getLogBuffer(),
            $this->getParams(),
            $this->getStatusChanger(),
            [],
            $response->getResponseData(),
        );

        $responseObj->process();

        return 'Item was Stopped';
    }

    protected function processFail(
        \M2E\Otto\Model\Connector\Response $response
    ): void {
    }

    protected function getActionNick(): string
    {
        return \M2E\Otto\Model\Otto\Listing\Product\Action\Async\DefinitionsCollection::ACTION_DELETE;
    }
}
