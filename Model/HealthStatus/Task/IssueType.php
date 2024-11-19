<?php

declare(strict_types=1);

namespace M2E\Otto\Model\HealthStatus\Task;

/**
 * Class \M2E\Otto\Model\HealthStatus\Task\IssueType
 */
abstract class IssueType extends AbstractModel
{
    public const TYPE = 'issue';

    //########################################

    public function getType()
    {
        return self::TYPE;
    }

    public function mustBeShownIfSuccess()
    {
        return false;
    }

    //########################################
}
