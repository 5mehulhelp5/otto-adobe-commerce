<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise;

use M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidator;

class ProcessStart extends \M2E\Otto\Model\Otto\Listing\Product\Action\Async\AbstractProcessStart
{
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\Request $request;
    private RequestFactory $requestFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidatorFactory $actionValidatorFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidator $actionValidator;

    public function __construct(
        RequestFactory $requestFactory,
        ValidatorFactory $actionValidatorFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->actionValidatorFactory = $actionValidatorFactory;
    }

    protected function getActionNick(): string
    {
        return \M2E\Otto\Model\Otto\Listing\Product\Action\Async\DefinitionsCollection::ACTION_REVISE;
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

    protected function getCommand(): \M2E\Otto\Model\Connector\CommandProcessingInterface
    {
        $requestData = $this->getRequest()->build(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->getLogBuffer(),
            $this->getParams()
        );

        return new \M2E\Otto\Model\Otto\Connector\Item\ReviseCommand(
            $this->getAccount()->getServerHash(),
            $requestData->getData(),
        );
    }

    protected function getRequestMetadata(): array
    {
        return $this->getRequest()->getMetadata();
    }

    private function getRequest(): \M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise\Request
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->request)) {
            return $this->request;
        }

        return $this->request = $this->requestFactory->create();
    }
}
