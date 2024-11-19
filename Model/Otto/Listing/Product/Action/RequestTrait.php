<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

trait RequestTrait
{
    private function processDataProviderLogs(\M2E\Otto\Model\Product\DataProvider $dataProvider): void
    {
        foreach ($dataProvider->getLogs() as $log) {
            $this->addWarningMessage($log);
        }
    }
}
