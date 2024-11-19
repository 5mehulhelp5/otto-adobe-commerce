<?php

namespace M2E\Otto\Model\ResourceModel\Tag\ListingProduct\Relation;

use M2E\Otto\Model\ResourceModel\Tag\ListingProduct\Relation as ResourceModel;
use M2E\Otto\Model\Tag\ListingProduct\Relation;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    /**
     * @inerhitDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Relation::class, ResourceModel::class);
    }
}
