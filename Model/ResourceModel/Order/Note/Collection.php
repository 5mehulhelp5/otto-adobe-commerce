<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Order\Note;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public const ORDER_ID_FIELD = 'order_id';

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Order\Note::class,
            \M2E\Otto\Model\ResourceModel\Order\Note::class
        );
    }

    /**
     * @return \M2E\Otto\Model\Order\Note[]
     */
    public function getItems()
    {
        /** @var \M2E\Otto\Model\Order\Note[] $items */
        $items = parent::getItems();

        return $items;
    }
}
