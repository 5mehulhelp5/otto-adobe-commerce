<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping;

class ShippingDiffStub extends \M2E\Otto\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return true;
    }

    public function isShippingDifferent(): bool
    {
        return true;
    }
}
