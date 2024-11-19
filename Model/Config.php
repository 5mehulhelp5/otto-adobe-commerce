<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

class Config extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\M2E\Otto\Model\ResourceModel\Config::class);
    }

    public function setGroup(string $group): self
    {
        $this->setData('group', $group);

        return $this;
    }

    public function getGroup(): string
    {
        return (string)$this->getData('group');
    }

    public function setKey(string $key): self
    {
        $this->setData('key', $key);

        return $this;
    }

    public function getKey(): string
    {
        return (string)$this->getData('key');
    }

    public function setValue($value): self
    {
        $this->setData('value', $value);

        return $this;
    }

    public function getValue()
    {
        return $this->getData('value');
    }
}
