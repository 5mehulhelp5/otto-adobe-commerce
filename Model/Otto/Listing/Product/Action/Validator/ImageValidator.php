<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class ImageValidator implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?string
    {
        $providerResult = $product->getDataProvider()->getImages();

        if ($providerResult->getValue()->mainImage === null) {
            return (string)__('Main Image is missing a value.');
        }

        return null;
    }
}
