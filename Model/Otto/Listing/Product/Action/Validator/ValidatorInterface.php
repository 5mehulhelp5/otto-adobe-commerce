<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Validator;

interface ValidatorInterface
{
    public function validate(\M2E\Otto\Model\Product $product): ?ValidatorMessage;
}
