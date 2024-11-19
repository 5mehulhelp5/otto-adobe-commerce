<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel;

class Registry extends \M2E\Otto\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        $this->_init(\M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_REGISTRY, 'id');
    }

    /**
     * @param string $key
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByKey(string $key): void
    {
        $this->getConnection()
             ->delete($this->getMainTable(), "`key` = '$key'");
    }
}
