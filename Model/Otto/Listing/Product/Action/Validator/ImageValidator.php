<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class ImageValidator implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?ValidatorMessage
    {
        $providerResult = $product->getDataProvider()->getImages();

        if ($providerResult->getValue()->mainImage === null) {
            return new ValidatorMessage(
                (string)__('Main Image is missing a value.'),
                \M2E\Otto\Model\Tag\ValidatorIssues::ERROR_MAIN_IMAGE_MISSING
            );
        }

        return null;
    }
}
