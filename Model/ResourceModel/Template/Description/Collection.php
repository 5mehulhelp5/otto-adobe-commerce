<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Template\Description;

class Collection extends \M2E\Otto\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Otto\Model\Template\Description::class,
            \M2E\Otto\Model\ResourceModel\Template\Description::class
        );
    }
}
