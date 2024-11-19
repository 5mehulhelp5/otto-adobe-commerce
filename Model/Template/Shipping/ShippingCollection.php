<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping;

class ShippingCollection
{
    /** @var \M2E\Otto\Model\Template\Shipping[] */
    private array $shippings = [];

    public function add(\M2E\Otto\Model\Template\Shipping $shipping): self
    {
        $this->shippings[$shipping->getShippingProfileId()] = $shipping;

        return $this;
    }

    public function has(string $id): bool
    {
        return isset($this->shippings[$id]);
    }

    public function get(string $id): \M2E\Otto\Model\Template\Shipping
    {
        return $this->shippings[$id];
    }

    public function remove(string $id): self
    {
        unset($this->shippings[$id]);

        return $this;
    }

    /**
     * @return \M2E\Otto\Model\Template\Shipping[]
     */
    public function getAll(): array
    {
        return array_values($this->shippings);
    }
}
