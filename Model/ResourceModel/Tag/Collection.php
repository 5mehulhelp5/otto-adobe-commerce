<?php

namespace M2E\Otto\Model\ResourceModel\Tag;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    /**
     * @inerhitDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Tag\Entity::class,
            \M2E\Otto\Model\ResourceModel\Tag::class
        );
    }

    /**
     * @return \M2E\Otto\Model\Tag\Entity[]
     */
    public function getItemsWithoutHasErrorsTag(): array
    {
        $this->getSelect()->where('error_code != (?)', \M2E\Otto\Model\Tag::HAS_ERROR_ERROR_CODE);

        return $this->getAll();
    }

    /**
     * @return \M2E\Otto\Model\Tag\Entity[]
     */
    public function getAll(): array
    {
        return $this->getItems();
    }
}
