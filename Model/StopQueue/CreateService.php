<?php

declare(strict_types=1);

namespace M2E\Otto\Model\StopQueue;

class CreateService
{
    private \M2E\Otto\Model\StopQueueFactory $stopQueueFactory;
    private Repository $repository;
    private \M2E\Otto\Helper\Module\Exception $helperException;
    private \M2E\Otto\Helper\Module\Logger $logger;

    public function __construct(
        \M2E\Otto\Model\StopQueueFactory $stopQueueFactory,
        \M2E\Otto\Model\StopQueue\Repository $repository,
        \M2E\Otto\Helper\Module\Exception $helperException,
        \M2E\Otto\Helper\Module\Logger $logger
    ) {
        $this->stopQueueFactory = $stopQueueFactory;
        $this->repository = $repository;
        $this->helperException = $helperException;
        $this->logger = $logger;
    }

    public function create(\M2E\Otto\Model\Product $listingProduct): void
    {
        if (!$listingProduct->isStoppable()) {
            return;
        }

        try {
            $stopQueue = $this->stopQueueFactory->create();
            $stopQueue->create(
                $listingProduct->getAccount()->getServerHash(),
                $listingProduct->getOttoProductSku(),
            );
            $this->repository->create($stopQueue);
        } catch (\Throwable $exception) {
            $sku = $listingProduct->getOttoProductSku();

            $this->logger->process(
                sprintf(
                    'Product [Listing Product ID: %s, SKU %s] was not added to stop queue because of the error: %s',
                    $listingProduct->getId(),
                    $sku,
                    $exception->getMessage()
                ),
                'Product was not added to stop queue'
            );

            $this->helperException->process($exception);
        }
    }
}
