<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

class Action
{
    private const ACTION_NOTHING = 0;

    private int $action;
    private \M2E\Otto\Model\Product $product;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator;

    private function __construct(
        int $action,
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator
    ) {
        $this->product = $product;
        $this->configurator = $configurator;
        $this->action = $action;
    }

    public function getProduct(): \M2E\Otto\Model\Product
    {
        return $this->product;
    }

    public function getConfigurator(): \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator
    {
        return $this->configurator;
    }

    public function isActionList(): bool
    {
        return $this->action === \M2E\Otto\Model\Product::ACTION_LIST;
    }

    public function isActionRevise(): bool
    {
        return $this->action === \M2E\Otto\Model\Product::ACTION_REVISE;
    }

    public function isActionStop(): bool
    {
        return $this->action === \M2E\Otto\Model\Product::ACTION_STOP;
    }

    public function isActionRelist(): bool
    {
        return $this->action === \M2E\Otto\Model\Product::ACTION_RELIST;
    }

    public function isActionNothing(): bool
    {
        return $this->action === self::ACTION_NOTHING;
    }

    // ----------------------------------------

    public static function createNothing(\M2E\Otto\Model\Product $product): self
    {
        return new self(
            self::ACTION_NOTHING,
            $product,
            new \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator(),
        );
    }

    public static function createList(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator
    ): self {
        return new self(
            \M2E\Otto\Model\Product::ACTION_LIST,
            $product,
            $configurator,
        );
    }

    public static function createRelist(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator
    ): self {
        return new self(
            \M2E\Otto\Model\Product::ACTION_RELIST,
            $product,
            $configurator,
        );
    }

    public static function createRevise(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator
    ): self {
        return new self(
            \M2E\Otto\Model\Product::ACTION_REVISE,
            $product,
            $configurator,
        );
    }

    public static function createStop(
        \M2E\Otto\Model\Product $product
    ): self {
        return new self(
            \M2E\Otto\Model\Product::ACTION_STOP,
            $product,
            new \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator(),
        );
    }
}
