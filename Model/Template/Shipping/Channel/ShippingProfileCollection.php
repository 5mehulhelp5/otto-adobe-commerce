<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping\Channel;

class ShippingProfileCollection
{
    /** @var \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile[] */
    private array $shippingProfiles = [];

    public function add(\M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile $shippingProfile): self
    {
        $this->shippingProfiles[$shippingProfile->getShippingProfileId()] = $shippingProfile;

        return $this;
    }

    public function has(?string $id): bool
    {
        return isset($this->shippingProfiles[$id]);
    }

    public function get(string $id): \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile
    {
        return $this->shippingProfiles[$id];
    }

    public function remove(string $id): self
    {
        unset($this->shippingProfiles[$id]);

        return $this;
    }

    /**
     * @return \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile[]
     */
    public function getAll(): array
    {
        return array_values($this->shippingProfiles);
    }
}
