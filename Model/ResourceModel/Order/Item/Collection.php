<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Order\Item;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Order\Item::class,
            \M2E\Otto\Model\ResourceModel\Order\Item::class
        );
    }

    /**
     * @return \M2E\Otto\Model\Order\Item[]
     */
    public function getItems()
    {
        /** @var \M2E\Otto\Model\Order\Item[] $items */
        $items = parent::getItems();

        return $items;
    }
}
