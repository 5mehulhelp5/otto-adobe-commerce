<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Tag;

class ValidatorIssues
{
    public const NOT_USER_ERROR = 'not-user-error';

    public const ERROR_NO_DESCRIPTION_POLICY = '0001-m2e';
    public const ERROR_NO_SHIPPING_POLICY = '0002-m2e';
    public const ERROR_BRAND_INVALID_OR_MISSING = '0003-m2e';
    public const ERROR_SHIPPING_PROFILE_INVALID = '0004-m2e';
    public const ERROR_EAN_MISSING = '0005-m2e';
    public const ERROR_MAIN_IMAGE_MISSING = '0006-m2e';
    public const ERROR_ZERO_PRICE = '0007-m2e';
    public const ERROR_DUPLICATE_SKU_IN_UNMANAGED = '0008-m2e';
    public const ERROR_DUPLICATE_SKU_IN_LISTING = '0009-m2e';
    public const ERROR_HANDLING_TIME_OUT_OF_RANGE = '0010-m2e';
    public const ERROR_HANDLING_TIME_INVALID = '0011-m2e';
    public const ERROR_ZERO_QTY = '0012-m2e';
    public const ERROR_QUANTITY_POLICY_CONTRADICTION = '0015-m2e';

    public function mapByCode(string $code): ?\M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorMessage
    {
        $map = [
            self::ERROR_NO_DESCRIPTION_POLICY => (string)__('No Description policy is set for this Listing.'),
            self::ERROR_NO_SHIPPING_POLICY => (string)__('No Shipping policy is set for this Listing.'),
            self::ERROR_BRAND_INVALID_OR_MISSING => (string)__('Brand is not valid or missing a value.'),
            self::ERROR_SHIPPING_PROFILE_INVALID => (string)__('The Shipping Profile assigned to this product is no longer available.'),
            self::ERROR_EAN_MISSING => (string)__('EAN is missing a value.'),
            self::ERROR_MAIN_IMAGE_MISSING => (string)__('Main Image is missing.'),
            self::ERROR_ZERO_PRICE => (string)__('The Product Price cannot be 0.'),
            self::ERROR_DUPLICATE_SKU_IN_UNMANAGED => (string)__('Product with the same SKU already exists in Unmanaged Items.'),
            self::ERROR_DUPLICATE_SKU_IN_LISTING => (string)__('Product with the same SKU already exists in another Listing.'),
            self::ERROR_HANDLING_TIME_OUT_OF_RANGE => (string)__('Handling Time must be positive whole number less than 1000.'),
            self::ERROR_HANDLING_TIME_INVALID => (string)__('Handling Time is missing or invalid.'),
            self::ERROR_ZERO_QTY => (string)__('The Product Quantity must be greater than 0.'),
            self::ERROR_QUANTITY_POLICY_CONTRADICTION => (string)__('You\'re submitting an item with QTY contradicting the QTY settings in your Selling Policy.'),
        ];

        if (!isset($map[$code])) {
            return null;
        }

        return new \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorMessage(
            $map[$code],
            $code
        );
    }
}
