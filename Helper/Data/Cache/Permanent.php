<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Data\Cache;

class Permanent implements BaseInterface
{
    private \M2E\Core\Model\Cache\Adapter $adapter;
    private \M2E\Core\Model\Cache\AdapterFactory $adapterFactory;

    public function __construct(\M2E\Core\Model\Cache\AdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    public function getValue(string $key)
    {
        return $this->getAdapter()->get($key);
    }

    public function setValue(string $key, $value, array $tags = [], $lifetime = null): void
    {
        if ($lifetime === null || $lifetime <= 0) {
            $lifetime = 60 * 60 * 24;
        }

        $this->getAdapter()->set($key, $value, $lifetime, $tags);
    }

    public function removeValue(string $key): void
    {
        $this->getAdapter()->remove($key);
    }

    public function removeAllValues(): void
    {
        $this->getAdapter()->removeAllValues();
    }

    public function removeTagValues(string $tag): void
    {
        $this->getAdapter()->removeByTag($tag);
    }

    public function getAdapter(): \M2E\Core\Model\Cache\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory
                ->create(\M2E\Otto\Helper\Module::IDENTIFIER);
        }

        return $this->adapter;
    }
}
