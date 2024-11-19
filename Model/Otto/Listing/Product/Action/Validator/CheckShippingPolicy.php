<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class CheckShippingPolicy implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?string
    {
        $shippingTemplate = $product->getShippingTemplate();

        if ($shippingTemplate->isShippingProfileDeleted()) {
            return (string)__(
                'The Shipping Profile assigned to this product is no longer available.' .
                ' Please assign a valid Shipping Profile and try again.'
            );
        }

        return null;
    }
}
