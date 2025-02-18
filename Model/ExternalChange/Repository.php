<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ExternalChange;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\ExternalChange $resource;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\ExternalChange $resource
    ) {
        $this->resource = $resource;
    }

    public function create(\M2E\Otto\Model\ExternalChange $externalChanges): void
    {
        $this->resource->save($externalChanges);
    }

    public function remove(\M2E\Otto\Model\ExternalChange $externalChanges): void
    {
        $this->resource->delete($externalChanges);
    }

    public function removeAllByAccount(int $accountId): void
    {
        $this->resource->getConnection()->delete(
            $this->resource->getMainTable(),
            [
                \M2E\Otto\Model\ResourceModel\ExternalChange::COLUMN_ACCOUNT_ID . ' = ?' => $accountId,
            ]
        );
    }
}
