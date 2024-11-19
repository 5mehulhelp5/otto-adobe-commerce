<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Delete;

class ValidatorFactory extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidatorFactory
{
    protected function getValidatorClass(): string
    {
        return Validator::class;
    }
}
