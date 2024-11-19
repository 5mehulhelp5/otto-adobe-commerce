<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Revise;

class Validator extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidator
{
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\PriceValidator $priceValidator;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\EanValidator $eanValidator;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ImageValidator $imageValidator;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\CheckShippingPolicy $checkShippingPolicy;

    public function __construct(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\PriceValidator $priceValidator,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\EanValidator $eanValidator,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ImageValidator $imageValidator,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\CheckShippingPolicy $checkShippingPolicy
    ) {
        $this->checkShippingPolicy = $checkShippingPolicy;
        $this->imageValidator = $imageValidator;
        $this->eanValidator = $eanValidator;
        $this->priceValidator = $priceValidator;
        $this->shippingHandlingTime = $shippingHandlingTime;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isRevisable()) {
            $this->addMessage('Item is not Listed or not available');

            return false;
        }

        if (empty($this->getListingProduct()->getOttoProductSKU())) {
            return false;
        }

        if ($error = $this->checkShippingPolicy->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->priceValidator->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->shippingHandlingTime->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($this->getConfigurator()->isDetailsAllowed()) {
            if ($error = $this->eanValidator->validate($this->getListingProduct())) {
                $this->addMessage($error);

                return false;
            }

            if ($error = $this->imageValidator->validate($this->getListingProduct())) {
                $this->addMessage($error);

                return false;
            }
        }

        return true;
    }
}
