<?php

declare(strict_types=1);

namespace M2E\Otto\Model\AttributeMapping\Gpsr\CategoryModifier;

class CategoryDiffStub extends \M2E\Otto\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return true;
    }

    public function isCategoriesDifferent(): bool
    {
        return true;
    }
}
