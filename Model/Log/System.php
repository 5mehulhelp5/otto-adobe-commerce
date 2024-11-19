<?php

namespace M2E\Otto\Model\Log;

/**
 * Class \M2E\Otto\Model\Log\System
 */
class System extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public const TYPE_LOGGER = 100;
    public const TYPE_EXCEPTION = 200;
    public const TYPE_EXCEPTION_CONNECTOR = 201;
    public const TYPE_FATAL_ERROR = 300;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\Otto\Model\ResourceModel\Log\System::class);
    }

    //########################################

    public function getType()
    {
        return $this->getData('type');
    }

    public function getClass()
    {
        return $this->getData('class');
    }

    public function getDescription()
    {
        return $this->getData('description');
    }

    public function getDetailedDescription()
    {
        return $this->getData('detailed_description');
    }

    public function getAdditionalData()
    {
        return $this->getData('additional_data');
    }

    //########################################
}
