<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\ResourceModel\ExternalChange as ExternalChangeResource;

class ExternalChange extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ExternalChangeResource::class);
    }

    public function init(
        \M2E\Otto\Model\Account $account,
        string $sku
    ): self {
        $this
            ->setData(\M2E\Otto\Model\ResourceModel\ExternalChange::COLUMN_ACCOUNT_ID, $account->getId())
            ->setData(ExternalChangeResource::COLUMN_SKU, $sku);

        return $this;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ExternalChangeResource::COLUMN_ACCOUNT_ID);
    }

    public function getSku(): string
    {
        return $this->getData(ExternalChangeResource::COLUMN_SKU);
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Otto\Helper\Date::createDateGmt(
            $this->getData(ExternalChangeResource::COLUMN_CREATE_DATE),
        );
    }
}
