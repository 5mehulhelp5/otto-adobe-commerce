<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

class Registry extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\Otto\Model\ResourceModel\Registry::class);
    }

    public function setKey(string $key): Registry
    {
        return $this->setData('key', $key);
    }

    public function setValue($value): Registry
    {
        return $this->setData('value', $value);
    }

    /**
     * @return array|mixed|null
     */
    public function getValue()
    {
        return $this->getData('value');
    }
}
