<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Config;

class Manager
{
    private \M2E\Core\Model\Config\Adapter $adapter;

    private \M2E\Core\Model\Config\AdapterFactory $adapterFactory;
    private \M2E\Otto\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\Core\Model\Config\AdapterFactory $adapterFactory,
        \M2E\Otto\Helper\Data\Cache\Permanent $cache
    ) {
        $this->adapterFactory = $adapterFactory;
        $this->cache = $cache;
    }

    public function getGroupValue(string $group, string $key)
    {
        return $this->getAdapter()->get($group, $key);
    }

    public function setGroupValue(string $group, string $key, $value): void
    {
        $this->getAdapter()->set($group, $key, $value);
    }

    public function hasGroupValue(string $group, string $key): bool
    {
        return $this->getAdapter()->has($group, $key);
    }

    public function getAdapter(): \M2E\Core\Model\Config\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory
                ->create(
                    \M2E\Otto\Helper\Module::IDENTIFIER,
                    $this->cache->getAdapter()
                );
        }

        return $this->adapter;
    }
}
