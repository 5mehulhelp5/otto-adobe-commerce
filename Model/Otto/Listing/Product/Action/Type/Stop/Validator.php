<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Stop;

class Validator extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractValidator
{
    public function validate(): bool
    {
        if (!$this->getListingProduct()->isStoppable()) {
            $this->addMessage(
                new \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorMessage(
                    'Item is not Listed or not available',
                    \M2E\Otto\Model\Tag\ValidatorIssues::NOT_USER_ERROR
                )
            );

            return false;
        }

        return true;
    }
}
