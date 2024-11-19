<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

use M2E\Otto\Model\Otto\Listing\Product\Action\Manual;

class RunStopAndRemoveProducts extends \M2E\Otto\Controller\Adminhtml\Otto\Listing\AbstractAction
{
    private Manual\Realtime\StopAndRemoveAction $realtimeStopAndRemoveAction;
    private Manual\Schedule\StopAndRemoveAction $scheduledStopAndRemoveAction;

    public function __construct(
        Manual\Realtime\StopAndRemoveAction $realtimeStopAndRemoveAction,
        Manual\Schedule\StopAndRemoveAction $scheduledStopAndRemoveAction,
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeStopAndRemoveAction = $realtimeStopAndRemoveAction;
        $this->scheduledStopAndRemoveAction = $scheduledStopAndRemoveAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeStopAndRemoveAction,
                ['remove' => true],
            );
        }

        return $this->createScheduleAction(
            $this->scheduledStopAndRemoveAction,
            ['remove' => true],
        );
    }
}
