<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing\RetrieveData;

class Simple
{
    private const MAX_PROCESSING_IDS_PER_REQUEST = 100;

    private \M2E\Otto\Model\Processing\Repository $repository;
    private \M2E\Otto\Model\Connector\Client\Single $client;
    private \M2E\Otto\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\Otto\Model\Processing\Repository $repository,
        \M2E\Otto\Model\Connector\Client\Single $client,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper
    ) {
        $this->repository = $repository;
        $this->client = $client;
        $this->exceptionHelper = $exceptionHelper;
    }

    public function process(): void
    {
        $borderData = \M2E\Core\Helper\Date::createCurrentGmt()
                                           ->modify('+ 5 minutes');

        $readyToDownload = $this->repository->findSimpleForDownloadData($borderData);
        foreach (array_chunk($readyToDownload, self::MAX_PROCESSING_IDS_PER_REQUEST) as $processings) {
            try {
                $this->processRecords($processings);
            } catch (\Throwable $e) {
                $this->exceptionHelper->process($e);
            }
        }
    }

    /**
     * @param \M2E\Otto\Model\Processing[] $processings
     *
     * @return void
     * @throws \M2E\Otto\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection|\JsonException
     */
    private function processRecords(array $processings): void
    {
        $ids = array_map(static fn(\M2E\Otto\Model\Processing $p) => $p->getServerHash(), $processings);
        $command = new \M2E\Otto\Model\Processing\Connector\SimpleGetResultCommand($ids);

        /** @var \M2E\Otto\Model\Processing\Connector\ResultCollection $resultCollection */
        $resultCollection = $this->client->process($command);

        foreach ($processings as $processing) {
            if ($resultCollection->has($processing->getServerHash())) {
                $result = $resultCollection->get($processing->getServerHash());
            } else {
                $result = \M2E\Otto\Model\Processing\Connector\Result::createNotFound($processing->getServerHash());
            }

            if ($result->isStatusNotFound()) {
                $processing->failDownload($this->getFailedMessage());
                $this->repository->save($processing);

                continue;
            }

            if (!$result->isStatusCompleted()) {
                continue;
            }

            $data = $result->getData() ?? [];
            if (!empty($data)) {
                $processing->setSimpleResultData($data);
            }

            $processing->completeDownload($result->getMessages() ?? []);
            $this->repository->save($processing);
        }
    }

    private function getFailedMessage(): \M2E\Core\Model\Connector\Response\Message
    {
        $message = new \M2E\Core\Model\Connector\Response\Message();
        $message->initFromPreparedData(
            'Request wait timeout exceeded.',
            \M2E\Core\Model\Response\Message::TYPE_ERROR,
        );

        return $message;
    }
}
