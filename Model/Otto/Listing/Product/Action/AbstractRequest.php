<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

abstract class AbstractRequest
{
    private RequestData $requestData;
    private array $warningMessages = [];
    private array $metadata = [];

    // ----------------------------------------

    public function build(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $actionConfigurator,
        array $params
    ): RequestData {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->requestData)) {
            return $this->requestData;
        }

        $data = $this->getActionData($product, $actionConfigurator, $params);
        $this->metadata = $this->getActionMetadata();

        $requestData = new RequestData($data);

        return $this->requestData = $requestData;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    // ----------------------------------------

    abstract protected function getActionData(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $actionConfigurator,
        array $params
    ): array;

    abstract protected function getActionMetadata(): array;

    // ----------------------------------------

    protected function addWarningMessage(string $message): void
    {
        $this->warningMessages[sha1($message)] = $message;
    }

    /**
     * @return string[]
     */
    public function getWarningMessages(): array
    {
        return array_values($this->warningMessages);
    }
}
