<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

class RunStopProducts extends \M2E\Otto\Controller\Adminhtml\Otto\Listing\AbstractAction
{
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime\StopAction $realtimeStopAction;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule\StopAction $scheduledStopAction;

    public function __construct(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime\StopAction $realtimeStopAction,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule\StopAction $scheduledStopAction,
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeStopAction = $realtimeStopAction;
        $this->scheduledStopAction = $scheduledStopAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeStopAction,
            );
        }

        return $this->createScheduleAction(
            $this->scheduledStopAction,
        );
    }
}
