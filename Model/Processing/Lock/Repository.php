<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Processing\Lock;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Processing\Lock\CollectionFactory $collectionFactory;
    private \M2E\Otto\Helper\Module\Database\Tables $tablesHelper;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Processing\Lock\CollectionFactory $collectionFactory,
        \M2E\Otto\Helper\Module\Database\Tables $tablesHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->tablesHelper = $tablesHelper;
    }

    public function create(\M2E\Otto\Model\Processing\Lock $lock): void
    {
        $lock->save();
    }

    public function remove(\M2E\Otto\Model\Processing\Lock $lock): void
    {
        $lock->delete();
    }

    public function isExist(string $objNick, int $objId): bool
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('object_nick', $objNick)
            ->addFieldToFilter('object_id', $objId)
            ->setPageSize(1);

        return !empty($collection->getItems());
    }

    /**
     * @param string $objNick
     * @param int $objId
     *
     * @return \M2E\Otto\Model\Processing\Lock[]
     */
    public function findByObjNameAndObjId(string $objNick, int $objId): array
    {
        return $this->findByParams($objNick, $objId);
    }

    /**
     * @param string $objNick
     *
     * @return \M2E\Otto\Model\Processing\Lock[]
     */
    public function findByObjName(string $objNick): array
    {
        return $this->findByParams($objNick, null);
    }

    /**
     * @param string $objNick
     * @param int|null $objId
     *
     * @return \M2E\Otto\Model\Processing\Lock[]
     */
    private function findByParams(string $objNick, ?int $objId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('object_nick', $objNick);
        if ($objId !== null) {
            $collection->addFieldToFilter('object_id', $objId);
        }

        return array_values($collection->getItems());
    }

    public function findByProcessingAndNickAndId(
        \M2E\Otto\Model\Processing $processing,
        string $objNick,
        int $objId
    ): ?\M2E\Otto\Model\Processing\Lock {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('processing_id', $processing->getId())
            ->addFieldToFilter('object_nick', $objNick)
            ->addFieldToFilter('object_id', $objId)
            ->setPageSize(1);

        /** @var \M2E\Otto\Model\Processing\Lock $lock */
        $lock = $collection->getFirstItem();
        if (!$lock->isObjectNew()) {
            return $lock;
        }

        return null;
    }

    public function removeAllByProcessing(\M2E\Otto\Model\Processing $processing): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $this->tablesHelper->getFullName(
                \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING_LOCK,
            ),
            ['`processing_id` = ?' => $processing->getId()],
        );
    }

    /**
     * @return \M2E\Otto\Model\Processing\Lock[]
     */
    public function findMissedLocks(): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->getSelect()->joinLeft(
                [
                    'p' => $this->tablesHelper->getFullName(
                        \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING,
                    ),
                ],
                'p.id = main_table.processing_id',
                []
            );
        $collection->addFieldToFilter('p.id', ['null' => true]);

        return array_values($collection->getItems());
    }
}
