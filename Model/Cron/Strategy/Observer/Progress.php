<?php

namespace M2E\Otto\Model\Cron\Strategy\Observer;

use Magento\Framework\Event\Observer;

class Progress implements \Magento\Framework\Event\ObserverInterface
{
    private bool $isEnabled = false;

    private ?\M2E\Otto\Model\Lock\Item\Manager $lockItemManager = null;

    private \M2E\Otto\Model\Lock\Item\ProgressFactory $lockItemProgressFactory;

    public function __construct(
        \M2E\Otto\Model\Lock\Item\ProgressFactory $lockItemProgressFactory
    ) {
        $this->lockItemProgressFactory = $lockItemProgressFactory;
    }

    public function enable(): self
    {
        $this->isEnabled = true;

        return $this;
    }

    public function disable(): self
    {
        $this->isEnabled = false;

        return $this;
    }

    public function setLockItemManager(\M2E\Otto\Model\Lock\Item\Manager $lockItemManager): self
    {
        $this->lockItemManager = $lockItemManager;

        return $this;
    }

    public function execute(Observer $observer)
    {
        if (!$this->isEnabled) {
            return;
        }

        if ($this->lockItemManager === null) {
            throw new \M2E\Otto\Model\Exception\Logic('Lock Item Manager was not set.');
        }

        $eventName = $observer->getEvent()->getName();
        $progressNick = $observer->getEvent()->getProgressNick();

        $progress = $this->lockItemProgressFactory->create(
            $this->lockItemManager,
            $progressNick
        );

        if ($eventName === \M2E\Otto\Model\Cron\Strategy::PROGRESS_START_EVENT_NAME) {
            $progress->start();

            return;
        }

        if ($eventName === \M2E\Otto\Model\Cron\Strategy::PROGRESS_SET_PERCENTAGE_EVENT_NAME) {
            $percentage = $observer->getEvent()->getData('percentage');
            $progress->setPercentage($percentage);

            return;
        }

        if ($eventName === \M2E\Otto\Model\Cron\Strategy::PROGRESS_SET_DETAILS_EVENT_NAME) {
            $args = [
                'percentage' => $observer->getEvent()->getData('percentage'),
                'total' => $observer->getEvent()->getData('total'),
            ];
            $progress->setDetails($args);

            return;
        }

        if ($eventName === \M2E\Otto\Model\Cron\Strategy::PROGRESS_STOP_EVENT_NAME) {
            $progress->stop();
        }
    }
}
