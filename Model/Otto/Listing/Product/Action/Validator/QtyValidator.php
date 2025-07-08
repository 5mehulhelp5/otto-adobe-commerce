<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

class QtyValidator implements \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?ValidatorMessage
    {
        $qty = $product->getDataProvider()->getQty()->getValue();
        $clearQty = $product->getMagentoProduct()->getQty(true);

        if ($clearQty > 0 && $qty <= 0) {
            return new ValidatorMessage(
                "You're submitting an item with QTY contradicting the QTY settings in your Selling Policy. " .
                'Please check Minimum Quantity to Be Listed and Quantity Percentage options.',
                \M2E\Otto\Model\Tag\ValidatorIssues::ERROR_QUANTITY_POLICY_CONTRADICTION
            );
        }

        if ($qty === 0) {
            return new ValidatorMessage(
                (string)__('The Product Quantity must be greater than 0.'),
                \M2E\Otto\Model\Tag\ValidatorIssues::ERROR_ZERO_QTY
            );
        }

        return null;
    }
}
