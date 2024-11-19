<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing;

class PartialData extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\Otto\Model\ResourceModel\Processing\PartialData::class);
    }

    public function create(
        \M2E\Otto\Model\Processing $processing,
        array $data,
        int $partNumber
    ): self {
        $this->setData('processing_id', $processing->getId())
             ->setData('part_number', $partNumber)
             ->setData('data', json_encode($data, JSON_THROW_ON_ERROR));

        return $this;
    }

    public function getProcessingId(): int
    {
        return (int)$this->getData('processing_id');
    }

    public function getPartNumber(): int
    {
        return (int)$this->getData('part_number');
    }

    public function getResultData(): array
    {
        $data = $this->getData('data');
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }
}
