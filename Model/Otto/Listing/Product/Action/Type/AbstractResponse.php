<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type;

abstract class AbstractResponse
{
    private array $params = [];
    private array $requestMetaData = [];
    private array $responseData = [];
    private \M2E\Otto\Model\Product $listingProduct;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer $logBuffer;
    private int $statusChanger;
    private \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Otto\Model\Otto\TagFactory $tagFactory;

    public function __construct(
        \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Otto\Model\Otto\TagFactory $tagFactory
    ) {
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
    }

    abstract public function process(): void;

    abstract public function generateResultMessage(): void;

    public function setStatusChanger(int $statusChanger): void
    {
        $this->statusChanger = $statusChanger;
    }

    protected function getStatusChanger(): int
    {
        return $this->statusChanger;
    }

    public function setParams(array $params = []): void
    {
        $this->params = $params;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    // ---------------------------------------

    public function setListingProduct(\M2E\Otto\Model\Product $product): void
    {
        $this->listingProduct = $product;
    }

    protected function getProduct(): \M2E\Otto\Model\Product
    {
        return $this->listingProduct;
    }

    // ---------------------------------------

    public function setConfigurator(\M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $object): void
    {
        $this->configurator = $object;
    }

    protected function getConfigurator(): \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator
    {
        return $this->configurator;
    }

    // ---------------------------------------

    public function setResponseData(array $value): self
    {
        $this->responseData = $value;

        return $this;
    }

    protected function getResponseData(): array
    {
        return $this->responseData;
    }

    // ---------------------------------------

    public function setRequestMetaData(array $value): self
    {
        $this->requestMetaData = $value;

        return $this;
    }

    public function getRequestMetaData(): array
    {
        return $this->requestMetaData;
    }

    public function setLogBuffer($logBuffer): self
    {
        $this->logBuffer = $logBuffer;

        return $this;
    }

    public function getLogBuffer(): \M2E\Otto\Model\Otto\Listing\Product\Action\LogBuffer
    {
        return $this->logBuffer;
    }

    // ----------------------------------------

    protected function addTags($messages): void
    {
        $tags = [];
        foreach ($messages as $message) {
            $tags[] = $this->tagFactory->createByErrorCode((string)$message['code'], $message['title']);
        }

        if (!empty($tags)) {
            $tags[] = $this->tagFactory->createWithHasErrorCode();

            $this->tagBuffer->addTags($this->getProduct(), $tags);
            $this->tagBuffer->flush();
        }
    }
}
