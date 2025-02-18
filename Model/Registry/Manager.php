<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Registry;

class Manager
{
    private \M2E\Core\Model\Registry\Adapter $adapter;
    private \M2E\Core\Model\Registry\AdapterFactory $adapterFactory;

    public function __construct(\M2E\Core\Model\Registry\AdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    public function setValue(string $key, string $value): void
    {
        $this->getAdapter()->set($key, $value);
    }

    public function getValue(string $key): ?string
    {
        return $this->getAdapter()->get($key);
    }

    public function getValueFromJson($key): array
    {
        $value = $this->getValue($key);
        if ($value === null) {
            return [];
        }

        return json_decode($value, true);
    }

    public function deleteValue($key): void
    {
        $this->getAdapter()->delete($key);
    }

    public function getAdapter(): \M2E\Core\Model\Registry\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory
                ->create(\M2E\Otto\Helper\Module::IDENTIFIER);
        }

        return $this->adapter;
    }
}
