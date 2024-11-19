<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing\ProcessResult;

class Simple
{
    private const MAX_RECORDS_ON_RUN = 500;

    private \M2E\Otto\Model\Processing\Repository $repository;
    private \M2E\Otto\Model\Processing\ResultHandlerFactory $resultHandlerFactory;
    private \M2E\Otto\Helper\Module\Exception $exceptionHelper;
    private \M2E\Otto\Model\Processing\LockManagerFactory $lockManagerFactory;

    public function __construct(
        \M2E\Otto\Model\Processing\Repository $repository,
        \M2E\Otto\Model\Processing\ResultHandlerFactory $resultHandlerFactory,
        \M2E\Otto\Model\Processing\LockManagerFactory $lockManagerFactory,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper
    ) {
        $this->repository = $repository;
        $this->resultHandlerFactory = $resultHandlerFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->lockManagerFactory = $lockManagerFactory;
    }

    public function processExpired(): void
    {
        foreach ($this->repository->findSimpleTypeExpired() as $processing) {
            try {
                $lockManager = $this->lockManagerFactory->create($processing);

                $handler = $this->resultHandlerFactory->create($processing->getHandleNick());
                $handler->initialize($processing->getParams());
                $handler->processExpire();
                $handler->clearLock($lockManager);

                $this->repository->remove($processing);
            } catch (\Throwable $e) {
                $this->exceptionHelper->process($e);

                $this->repository->forceRemove($processing);
            }
        }
    }

    public function processData(): void
    {
        foreach ($this->repository->findSimpleTypeForProcess(self::MAX_RECORDS_ON_RUN) as $processing) {
            try {
                $lockManager = $this->lockManagerFactory->create($processing);

                $handler = $this->resultHandlerFactory->create($processing->getHandleNick());
                $handler->initialize($processing->getParams());
                $handler->processSuccess($processing->getSimpleResultData(), $processing->getResultMessages());
                $handler->clearLock($lockManager);

                $this->repository->remove($processing);
            } catch (\Throwable $e) {
                $this->exceptionHelper->process($e);

                $this->repository->forceRemove($processing);
            }
        }
    }
}
