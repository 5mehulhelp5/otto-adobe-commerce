<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Stop;

class ValidatorFactory extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidatorFactory
{
    protected function getValidatorClass(): string
    {
        return Validator::class;
    }
}
