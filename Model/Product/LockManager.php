<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product;

class LockManager
{
    private const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 3600; // 1 hour

    private LockFactory $lockFactory;
    private LockRepository $lockRepository;

    public function __construct(
        LockFactory $lockFactory,
        LockRepository $lockRepository
    ) {
        $this->lockFactory = $lockFactory;
        $this->lockRepository = $lockRepository;
    }

    public function isLocked(\M2E\Otto\Model\Product $product): bool
    {
        $lock = $this->lockRepository->findByProductId($product->getId());

        if ($lock === null) {
            return false;
        }

        if ($this->isInactiveMoreThanSeconds($lock, self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $this->unlock($product);

            return false;
        }

        return true;
    }

    public function isInactiveMoreThanSeconds(Lock $lockItem, $maxInactiveInterval): bool
    {
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $createDate = $lockItem->getCreateDate();

        return $createDate->getTimestamp() < ($currentDate->getTimestamp() - $maxInactiveInterval);
    }

    public function lock(\M2E\Otto\Model\Product $product, string $initiator): void
    {
        if ($this->isLocked($product)) {
            throw new \LogicException('Current product has already been locked.');
        }

        $lock = $this->lockFactory->create($product->getId(), $initiator, \M2E\Core\Helper\Date::createCurrentGmt());
        $this->lockRepository->create($lock);
    }

    public function unlock(\M2E\Otto\Model\Product $product): void
    {
        $lock = $this->lockRepository->findByProductId($product->getId());
        if ($lock === null) {
            return;
        }

        $this->lockRepository->remove($lock);
    }
}
