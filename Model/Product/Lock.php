<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

use M2E\Otto\Model\ResourceModel\Product\Lock as LockResource;

class Lock extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(LockResource::class);
    }

    public function init(int $productId, string $initiator, \DateTime $createDate): self
    {
        $this->setData(LockResource::COLUMN_PRODUCT_ID, $productId);
        $this->setData(LockResource::COLUMN_INITIATOR, $initiator);
        $this->setData(LockResource::COLUMN_CREATE_DATE, $createDate);

        return $this;
    }

    public function getInitiator()
    {
        return $this->getData('initiator');
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(LockResource::COLUMN_CREATE_DATE)
        );
    }
}
