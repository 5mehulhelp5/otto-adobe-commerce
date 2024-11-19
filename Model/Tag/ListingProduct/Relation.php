<?php

namespace M2E\Otto\Model\Tag\ListingProduct;

use M2E\Otto\Model\ResourceModel\Tag\ListingProduct\Relation as ResourceModel;

class Relation extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    /**
     * @inerhitDoc
     */
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ResourceModel::class);
    }

    /**
     * @return int
     */
    public function getTagId(): int
    {
        return (int)$this->getDataByKey(ResourceModel::COLUMN_TAG_ID);
    }

    /**
     * @return int
     */
    public function getListingProductId(): int
    {
        return (int)$this->getDataByKey(ResourceModel::COLUMN_LISTING_PRODUCT_ID);
    }
}
