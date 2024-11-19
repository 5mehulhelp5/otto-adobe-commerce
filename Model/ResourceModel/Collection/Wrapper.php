<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ResourceModel\Collection;

/**
 * Class \M2E\Otto\Model\ResourceModel\Collection\Wrapper
 */
class Wrapper extends \Magento\Framework\Data\Collection\AbstractDb
{
    //########################################

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->getSelect()) {
            return parent::load($printQuery, $logQuery);
        }

        return $this;
    }

    public function getResource()
    {
        return null;
    }

    public function setCustomSize($size)
    {
        $this->_totalRecords = $size;
    }

    public function setCustomIsLoaded($flag)
    {
        $this->_isCollectionLoaded = $flag;
    }

    //########################################
}
