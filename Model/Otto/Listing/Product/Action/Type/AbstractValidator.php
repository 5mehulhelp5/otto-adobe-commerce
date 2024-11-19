<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type;

use M2E\Otto\Model\Response\Message;
use M2E\Otto\Model\Otto\Listing\Product\Action\Configurator;

abstract class AbstractValidator
{
    private array $params = [];
    private array $messages = [];
    private Configurator $configurator;
    private \M2E\Otto\Model\Product $listingProduct;

    public function init(
        \M2E\Otto\Model\Product $listingProduct,
        Configurator $configurator,
        $params
    ): void {
        $this->listingProduct = $listingProduct;
        $this->configurator = $configurator;
        $this->params = $params;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    public function setConfigurator(Configurator $configurator): self
    {
        $this->configurator = $configurator;

        return $this;
    }

    protected function getConfigurator(): Configurator
    {
        return $this->configurator;
    }

    public function setListingProduct(\M2E\Otto\Model\Product $listingProduct): self
    {
        $this->listingProduct = $listingProduct;

        return $this;
    }

    protected function getListingProduct(): \M2E\Otto\Model\Product
    {
        return $this->listingProduct;
    }

    abstract public function validate(): bool;

    protected function addMessage($message, $type = Message::TYPE_ERROR): void
    {
        $this->messages[] = [
            'text' => $message,
            'type' => $type,
        ];
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function hasErrorMessages(): bool
    {
        foreach ($this->getMessages() as $message) {
            if ($message['type'] === Message::TYPE_ERROR) {
                return true;
            }
        }

        return false;
    }
}
