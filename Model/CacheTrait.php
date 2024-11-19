<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

trait CacheTrait
{
    private function makeCacheKey(\M2E\Otto\Model\ActiveRecord\AbstractModel $model, int $id): string
    {
        return \M2E\Otto\Helper\Client::getClassName($model) . '_' . $id;
    }

    private function getCacheDate(\M2E\Otto\Model\ActiveRecord\AbstractModel $model): array
    {
        return $model->getData();
    }

    private function initializeFromCache(\M2E\Otto\Model\ActiveRecord\AbstractModel $model, array $data): void
    {
        $model->setData($data);
        $model->setOrigData();
    }
}
