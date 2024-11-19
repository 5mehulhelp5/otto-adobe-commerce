<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class Processing extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING, 'id');
    }
}
