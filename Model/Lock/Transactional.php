<?php

namespace M2E\Otto\Model\Lock;

/**
 * Class \M2E\Otto\Model\Lock\Transactional
 */
class Transactional extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\Otto\Model\ResourceModel\Lock\Transactional::class);
    }

    public function getNick()
    {
        return $this->getData('nick');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }
}
