<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ScheduledAction;

class Stub extends \M2E\Otto\Model\ScheduledAction
{
    public function __construct()
    {
        // do not init parent construct
    }

    public function init(
        \M2E\Otto\Model\Product $listingProduct,
        int $action,
        int $statusChanger,
        array $data,
        bool $isForce = false,
        array $tags = [],
        ?\M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator = null
    ): \M2E\Otto\Model\ScheduledAction {
        throw new \M2E\Otto\Model\Exception\Logic('Unable init stub object');
    }

    public function getListingProduct(): \M2E\Otto\Model\Product
    {
        throw new \M2E\Otto\Model\Exception\Logic('Unable init stub object');
    }

    public function getListingProductId(): int
    {
        throw new \M2E\Otto\Model\Exception\Logic('Unable init stub object');
    }

    public function getActionType(): int
    {
        return 0;
    }

    public function isActionTypeList(): bool
    {
        return false;
    }

    public function isActionTypeRelist(): bool
    {
        return false;
    }

    public function isActionTypeRevise(): bool
    {
        return false;
    }

    public function isActionTypeStop(): bool
    {
        return false;
    }

    public function isActionTypeDelete(): bool
    {
        return false;
    }

    public function isForce(): bool
    {
        return false;
    }

    public function getTags(): array
    {
        return [];
    }

    public function getAdditionalData(): array
    {
        return [];
    }
}
