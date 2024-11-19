<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus\Task;

/**
 * Class \M2E\Otto\Model\HealthStatus\Task\AbstractModel
 */
abstract class AbstractModel extends \M2E\Otto\Model\AbstractModel
{
    //########################################

    public function mustBeShownIfSuccess()
    {
        return true;
    }

    //########################################

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @return Result
     */
    abstract public function process();

    //########################################
}
