<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

class RunReviseProducts extends \M2E\Otto\Controller\Adminhtml\Otto\Listing\AbstractAction
{
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime\ReviseAction $realtimeReviseAction;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule\ReviseAction $scheduleReviseAction;

    public function __construct(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime\ReviseAction $realtimeReviseAction,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule\ReviseAction $scheduleReviseAction,
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeReviseAction = $realtimeReviseAction;
        $this->scheduleReviseAction = $scheduleReviseAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeReviseAction,
            );
        }

        return $this->createScheduleAction(
            $this->scheduleReviseAction,
        );
    }
}
