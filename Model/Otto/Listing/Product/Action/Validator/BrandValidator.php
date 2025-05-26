<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class BrandValidator implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?ValidatorMessage
    {
        $resolveResult = $product->getDataProvider()->getBrand();
        if ($resolveResult->isSuccess()) {
            return null;
        }

        $error = (string)__('Brand is not valid');
        $errors = $resolveResult->getMessages();
        if (!empty($errors)) {
            $error = reset($errors);
        }

        return new ValidatorMessage(
            $error,
            \M2E\Otto\Model\Tag\ValidatorIssues::ERROR_BRAND_INVALID_OR_MISSING
        );
    }
}
