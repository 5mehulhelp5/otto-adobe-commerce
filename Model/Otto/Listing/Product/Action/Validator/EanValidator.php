<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class EanValidator implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?string
    {
        $providerResult = $product->getDataProvider()->getEan();
        if ($providerResult->isSuccess()) {
            return null;
        }

        $error = (string)__('EAN is missing a value.');
        if (!empty($errors = $providerResult->getMessages())) {
            $error = reset($errors);
        }

        return $error;
    }
}
