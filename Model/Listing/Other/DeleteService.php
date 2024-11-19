<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other;

class DeleteService
{
    private \M2E\Otto\Model\Listing\Other\Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function process(\M2E\Otto\Model\Listing\Other $other): void
    {
        $this->repository->remove($other);
    }
}
