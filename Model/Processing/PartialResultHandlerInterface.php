<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing;

interface PartialResultHandlerInterface extends SimpleResultHandlerInterface
{
    public function processPartialResult(array $partialData): void;
}
