<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus\Task;

/**
 * Class \M2E\Otto\Model\HealthStatus\Task\InfoType
 */
abstract class InfoType extends AbstractModel
{
    public const TYPE = 'info';

    //########################################

    public function getType()
    {
        return self::TYPE;
    }

    //########################################
}
