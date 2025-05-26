<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\ListAction;

class Validator extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidator
{
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\EanValidator $eanValidator;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\BrandValidator $brandValidator;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ImageValidator $imageValidator;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\PriceValidator $priceValidator;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\CheckShippingPolicy $checkShippingPolicy;

    public function __construct(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\SameSkuAlreadyExists $sameSkuAlreadyExists,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\EanValidator $eanValidator,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\BrandValidator $brandValidator,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ImageValidator $imageValidator,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\PriceValidator $priceValidator,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ShippingHandlingTime $shippingHandlingTime,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\CheckShippingPolicy $checkShippingPolicy
    ) {
        $this->checkShippingPolicy = $checkShippingPolicy;
        $this->priceValidator = $priceValidator;
        $this->imageValidator = $imageValidator;
        $this->brandValidator = $brandValidator;
        $this->eanValidator = $eanValidator;
        $this->sameSkuAlreadyExists = $sameSkuAlreadyExists;
        $this->shippingHandlingTime = $shippingHandlingTime;
    }

    public function validate(): bool
    {
        if (!$this->getListingProduct()->isListable()) {
            $this->addMessage(
                new \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorMessage(
                    (string)__('Item is Listed or not available'),
                    \M2E\Otto\Model\Tag\ValidatorIssues::NOT_USER_ERROR
                )
            );

            return false;
        }

        if (!$this->getListingProduct()->getListing()->isDescriptionPolicyExist()) {
            $this->addMessage(
                new \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorMessage(
                    (string)__('No Description policy is set for this M2E Listing. Please assign a Description policy to the Listing first.'),
                    \M2E\Otto\Model\Tag\ValidatorIssues::ERROR_NO_DESCRIPTION_POLICY
                )
            );

            return false;
        }

        if (!$this->getListingProduct()->getListing()->isShippingPolicyExist()) {
            $this->addMessage(
                new \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorMessage(
                    (string)__('No Shipping policy is set for this M2E Listing. Please assign a Shipping policy to the Listing first.'),
                    \M2E\Otto\Model\Tag\ValidatorIssues::ERROR_NO_SHIPPING_POLICY
                )
            );

            return false;
        }

        if ($error = $this->checkShippingPolicy->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->sameSkuAlreadyExists->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->priceValidator->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->eanValidator->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->brandValidator->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->imageValidator->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        if ($error = $this->shippingHandlingTime->validate($this->getListingProduct())) {
            $this->addMessage($error);

            return false;
        }

        return !$this->hasErrorMessages();
    }
}
