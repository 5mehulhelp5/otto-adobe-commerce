<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class PriceValidator implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?string
    {
        if ($product->getDataProvider()->getPrice()->getValue()->price === 0.0) {
            return (string)__(
                'The Product Price cannot be 0. Please enter a valid Price greater than 0 to update your Product on the channel.'
            );
        }

        return null;
    }
}
