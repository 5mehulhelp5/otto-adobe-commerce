<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other;

class OttoProductCollection
{
    /** @var OttoProduct[] */
    private array $products = [];

    public function empty(): bool
    {
        return empty($this->products);
    }

    public function has(string $sku): bool
    {
        return isset($this->products[$sku]);
    }

    public function add(OttoProduct $product): void
    {
        $this->products[$product->getSku()] = $product;
    }

    public function get(string $sku): OttoProduct
    {
        return $this->products[$sku];
    }

    public function remove(string $sku): void
    {
        unset($this->products[$sku]);
    }

    /**
     * @return \M2E\Otto\Model\Listing\Other\OttoProduct[]
     */
    public function getAll(): array
    {
        return array_values($this->products);
    }

    public function getProductsSKUs(): array
    {
        return array_keys($this->products);
    }
}
