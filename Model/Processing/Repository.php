<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing;

use Magento\Framework\Data\Collection;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Processing\CollectionFactory $collectionFactory;
    private PartialDataFactory $partialDataFactory;
    private \M2E\Otto\Model\ResourceModel\Processing\PartialData\CollectionFactory $partialDataCollectionFactory;
    private \M2E\Otto\Helper\Module\Database\Tables $tablesHelper;
    private \M2E\Otto\Model\Processing\Lock\Repository $lockRepository;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Processing\CollectionFactory $collectionFactory,
        \M2E\Otto\Model\Processing\PartialDataFactory $partialDataFactory,
        \M2E\Otto\Model\ResourceModel\Processing\PartialData\CollectionFactory $partialDataCollectionFactory,
        \M2E\Otto\Model\Processing\Lock\Repository $lockRepository,
        \M2E\Otto\Helper\Module\Database\Tables $tablesHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->partialDataFactory = $partialDataFactory;
        $this->partialDataCollectionFactory = $partialDataCollectionFactory;
        $this->tablesHelper = $tablesHelper;
        $this->lockRepository = $lockRepository;
    }

    public function create(\M2E\Otto\Model\Processing $processing): void
    {
        $processing->save();
    }

    public function save(\M2E\Otto\Model\Processing $processing): void
    {
        $processing->save();
    }

    public function remove(\M2E\Otto\Model\Processing $processing): void
    {
        if ($processing->isTypePartial()) {
            $this->removePartialData($processing);
        }

        $processing->delete();
    }

    private function removePartialData(\M2E\Otto\Model\Processing $processing): void
    {
        $collectionPartial = $this->partialDataCollectionFactory->create();
        $collectionPartial->getConnection()->delete(
            $this->tablesHelper->getFullName(
                \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING_PARTIAL_DATA,
            ),
            ['`processing_id` = ?' => $processing->getId()],
        );
    }

    public function forceRemove(\M2E\Otto\Model\Processing $processing): void
    {
        $this->lockRepository->removeAllByProcessing($processing);
        $this->remove($processing);
    }

    public function createPartialData(\M2E\Otto\Model\Processing $processing, int $partNumber, array $data): void
    {
        $part = $this->partialDataFactory->create();
        $part->create($processing, $data, $partNumber);

        $part->save();
    }

    /**
     * @param \M2E\Otto\Model\Processing $processing
     *
     * @return \M2E\Otto\Model\Processing\PartialData[]
     */
    public function getPartialData(\M2E\Otto\Model\Processing $processing): array
    {
        $collectionPartial = $this->partialDataCollectionFactory->create();
        $collectionPartial->addFieldToFilter('processing_id', $processing->getId());

        return array_values($collectionPartial->getItems());
    }

    /**
     * @param \DateTime $borderDate
     *
     * @return \M2E\Otto\Model\Processing[]
     */
    public function findPartialForDownloadData(\DateTime $borderDate): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('type', \M2E\Otto\Model\Processing::TYPE_PARTIAL)
            ->addFieldToFilter('create_date', ['lteq' => $borderDate->format('Y-m-d H:i:s')])
            ->addFieldToFilter(
                'stage',
                [
                    'in' => [
                        \M2E\Otto\Model\Processing::STAGE_WAIT_SERVER,
                        \M2E\Otto\Model\Processing::STAGE_DOWNLOAD,
                    ],
                ],
            )
            ->addFieldToFilter('is_completed', 0)
            ->setOrder('create_date', Collection::SORT_ORDER_ASC);

        return array_values($collection->getItems());
    }

    /**
     * @param int $limit
     *
     * @return \M2E\Otto\Model\Processing[]
     */
    public function findPartialTypeForProcess(int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('type', \M2E\Otto\Model\Processing::TYPE_PARTIAL)
            ->addFieldToFilter('stage', \M2E\Otto\Model\Processing::STAGE_WAIT_PROCESS)
            ->addFieldToFilter('is_completed', 0)
            ->setPageSize($limit);

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\Otto\Model\Processing[]
     */
    public function findPartialTypeExpired(): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('type', \M2E\Otto\Model\Processing::TYPE_PARTIAL)
            ->addFieldToFilter(
                'expiration_date',
                ['lt' => \M2E\Otto\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')],
            )
            ->addFieldToFilter(
                'stage',
                [
                    'in' => [
                        \M2E\Otto\Model\Processing::STAGE_WAIT_SERVER,
                        \M2E\Otto\Model\Processing::STAGE_DOWNLOAD,
                    ],
                ],
            )
            ->addFieldToFilter('is_completed', 0);

        return array_values($collection->getItems());
    }

    /**
     * @param \DateTime $borderDate
     *
     * @return \M2E\Otto\Model\Processing[]
     */
    public function findSimpleForDownloadData(\DateTime $borderDate): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('type', \M2E\Otto\Model\Processing::TYPE_SIMPLE)
            ->addFieldToFilter('create_date', ['lteq' => $borderDate->format('Y-m-d H:i:s')])
            ->addFieldToFilter(
                'stage',
                [
                    'in' => [
                        \M2E\Otto\Model\Processing::STAGE_WAIT_SERVER,
                    ],
                ],
            )
            ->addFieldToFilter('is_completed', 0)
            ->setOrder('create_date', Collection::SORT_ORDER_ASC);

        return array_values($collection->getItems());
    }

    /**
     * @param int $limit
     *
     * @return \M2E\Otto\Model\Processing[]
     */
    public function findSimpleTypeForProcess(int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('type', \M2E\Otto\Model\Processing::TYPE_SIMPLE)
            ->addFieldToFilter('stage', \M2E\Otto\Model\Processing::STAGE_WAIT_PROCESS)
            ->addFieldToFilter('is_completed', 0)
            ->setPageSize($limit);

        return array_values($collection->getItems());
    }

    /**
     * @return \M2E\Otto\Model\Processing[]
     */
    public function findSimpleTypeExpired(): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('type', \M2E\Otto\Model\Processing::TYPE_SIMPLE)
            ->addFieldToFilter(
                'expiration_date',
                ['lt' => \M2E\Otto\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')],
            )
            ->addFieldToFilter(
                'stage',
                [
                    'in' => [
                        \M2E\Otto\Model\Processing::STAGE_WAIT_SERVER,
                        \M2E\Otto\Model\Processing::STAGE_DOWNLOAD,
                    ],
                ],
            )
            ->addFieldToFilter('is_completed', 0);

        return array_values($collection->getItems());
    }

    /**
     * @param int[] $ids
     *
     * @return \M2E\Otto\Model\Processing[]
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('id', ['in' => array_unique($ids)]);

        return array_values($collection->getItems());
    }
}
