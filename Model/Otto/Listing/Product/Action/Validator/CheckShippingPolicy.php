<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class CheckShippingPolicy implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?ValidatorMessage
    {
        $shippingTemplate = $product->getShippingTemplate();

        if ($shippingTemplate->isShippingProfileDeleted()) {
            return new ValidatorMessage(
                (string)__('The Shipping Profile assigned to this product is no longer available.' .
                    ' Please assign a valid Shipping Profile and try again.'),
                \M2E\Otto\Model\Tag\ValidatorIssues::ERROR_SHIPPING_PROFILE_INVALID
            );
        }

        return null;
    }
}
