<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Exception;

use M2E\Otto\Model\Exception\Logic;

class NotImplementedException extends Logic
{
    public function __construct()
    {
        parent::__construct('This feature is not implemented yet.');
    }
}
