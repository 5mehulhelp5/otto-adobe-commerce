<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\ResourceModel\StopQueue as ResourceModel;

class StopQueue extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ResourceModel::class);
    }

    public function create(string $account, string $ottoProductSku): self
    {
        $this->setRequestData($account, $ottoProductSku);

        return $this;
    }

    public function setAsProcessed(): void
    {
        $this->setData(ResourceModel::COLUMN_IS_PROCESSED, 1);
    }

    public function getRequestData(): array
    {
        $data = $this->getData(ResourceModel::COLUMN_REQUEST_DATA);
        if ($data === null) {
            return [];
        }

        $data = json_decode($data, true);

        return [
            'account' => $data['account'],
            'otto_product_sku' => $data['otto_product_sku'],
            'action_date' => $data['action_date']
        ];
    }

    private function setRequestData(string $account, string $ottoProductSku): void
    {
        $this->setData(
            ResourceModel::COLUMN_REQUEST_DATA,
            json_encode([
                'account' => $account,
                'otto_product_sku' => $ottoProductSku,
                'action_date' => \M2E\Otto\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
            ], JSON_THROW_ON_ERROR)
        );
    }
}
