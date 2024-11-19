<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Instruction;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        $this->_init(
            \M2E\Otto\Model\Instruction::class,
            \M2E\Otto\Model\ResourceModel\Instruction::class
        );
    }
}
