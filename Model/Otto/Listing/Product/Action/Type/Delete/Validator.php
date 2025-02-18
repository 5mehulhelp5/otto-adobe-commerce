<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Delete;

class Validator extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidator
{
    private \M2E\Otto\Model\Product\RemoveHandler $removeHandler;

    public function __construct(
        \M2E\Otto\Model\Product\RemoveHandler $removeHandler
    ) {
        $this->removeHandler = $removeHandler;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isStoppable()) {
            $this->removeHandler->process($this->getListingProduct());
        }

        return true;
    }
}
