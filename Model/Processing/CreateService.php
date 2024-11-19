<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing;

class CreateService
{
    private \M2E\Otto\Model\ProcessingFactory $processingFactory;
    private \M2E\Otto\Model\Processing\Repository $repository;

    public function __construct(
        \M2E\Otto\Model\ProcessingFactory $processingFactory,
        Repository $repository
    ) {
        $this->processingFactory = $processingFactory;
        $this->repository = $repository;
    }

    public function createSimple(
        string $serverHash,
        string $handlerNick,
        array $params,
        \DateTime $expireDate
    ): \M2E\Otto\Model\Processing {
        return $this->create(
            $serverHash,
            $handlerNick,
            $params,
            $expireDate,
            \M2E\Otto\Model\Processing::TYPE_SIMPLE,
        );
    }

    public function createPartial(
        string $serverHash,
        string $handlerNick,
        array $params,
        \DateTime $expireDate
    ): \M2E\Otto\Model\Processing {
        return $this->create(
            $serverHash,
            $handlerNick,
            $params,
            $expireDate,
            \M2E\Otto\Model\Processing::TYPE_PARTIAL,
        );
    }

    private function create(
        string $serverHash,
        string $handlerNick,
        array $params,
        \DateTime $expireDate,
        int $type
    ): \M2E\Otto\Model\Processing {
        $processing = $this->processingFactory->create();

        $processing->create($type, $serverHash, $handlerNick, $params, $expireDate);

        $this->repository->create($processing);

        return $processing;
    }
}
