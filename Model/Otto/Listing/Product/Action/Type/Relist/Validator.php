<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Relist;

class Validator extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidator
{
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\PriceValidator $priceValidator;

    public function __construct(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\PriceValidator $priceValidator
    ) {
        $this->priceValidator = $priceValidator;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isRelistable()) {
            $this->addMessage('The Item either is Listed, or not Listed yet or not available');

            return false;
        }

        if ($error = $this->priceValidator->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        return true;
    }
}
