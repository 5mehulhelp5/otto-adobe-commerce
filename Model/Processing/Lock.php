<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing;

class Lock extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\Otto\Model\ResourceModel\Processing\Lock::class);
    }

    public function create(int $processingId, string $objectNick, int $objId, ?string $tag = null): self
    {
        $this->setData('processing_id', $processingId)
             ->setData('object_nick', $objectNick)
             ->setData('object_id', $objId)
             ->setData('tag', $tag);

        return $this;
    }

    public function getProcessingId(): int
    {
        return (int)$this->getData('processing_id');
    }

    public function getNick(): string
    {
        return $this->getData('object_nick');
    }

    public function getObjectId(): int
    {
        return (int)$this->getData('object_id');
    }

    public function getTag(): ?string
    {
        return $this->getData('tag');
    }
}
