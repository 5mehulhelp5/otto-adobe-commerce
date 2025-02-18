<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

class VariablesDir
{
    private \M2E\Core\Model\VariablesDir\Adapter $adapter;
    private \M2E\Core\Model\VariablesDir\AdapterFactory $adapterFactory;

    public function __construct(\M2E\Core\Model\VariablesDir\AdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    public function getBasePath(): string
    {
        return $this->getAdapter()->getBasePath();
    }

    public function getPath(): string
    {
        return $this->getAdapter()->getPath();
    }

    public function isBaseExist(): bool
    {
        return $this->getAdapter()->isBaseExist();
    }

    public function isExist(): bool
    {
        return $this->getAdapter()->isExist();
    }

    public function createBase(): void
    {
        $this->getAdapter()->createBase();
    }

    public function create(): void
    {
        $this->getAdapter()->create();
    }

    public function removeBase(): void
    {
        $this->getAdapter()->removeBase();
    }

    public function removeBaseForce(): void
    {
        $this->getAdapter()->removeBaseForce();
    }

    public function remove()
    {
        $this->getAdapter()->remove();
    }

    public function getAdapter(): \M2E\Core\Model\VariablesDir\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->adapterFactory
                ->create(\M2E\Otto\Helper\Module::IDENTIFIER);
        }

        return $this->adapter;
    }
}
