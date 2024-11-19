<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class ShippingHandlingTime implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?string
    {
        $providerResult = $product->getDataProvider()->getDelivery();
        if ($providerResult->isSuccess()) {
            $deliveryTime = $providerResult->getValue()->deliveryTime;
            if (
                $deliveryTime > 999
                || $deliveryTime < 1
            ) {
                return (string)__(
                    'Handling Time must be positive whole number less than 1000'
                );
            }

            return null;
        }

        $error = (string)__('Handling Time is missing or invalid.');
        if (!empty($errors = $providerResult->getMessages())) {
            $error = reset($errors);
        }

        return $error;
    }
}
