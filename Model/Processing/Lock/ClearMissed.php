<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing\Lock;

class ClearMissed
{
    private \M2E\Otto\Model\Processing\Lock\Repository $repository;
    private \M2E\Otto\Helper\Module\Logger $logger;

    public function __construct(
        Repository $repository,
        \M2E\Otto\Helper\Module\Logger $logger
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function process(): void
    {
        $lockData = [];
        foreach ($this->repository->findMissedLocks() as $lock) {
            $lockData[$lock->getNick()][$lock->getObjectId()] = $lock->getTag();

            $this->repository->remove($lock);
        }

        if (!empty($lockData)) {
            $this->logger->process($lockData, 'Processing Locks Records were broken and removed');
        }
    }
}
