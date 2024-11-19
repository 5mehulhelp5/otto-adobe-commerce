<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

class RunListProducts extends \M2E\Otto\Controller\Adminhtml\Otto\Listing\AbstractAction
{
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime\ListAction $realtimeListAction;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule\ListAction $scheduledListAction;

    public function __construct(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime\ListAction $realtimeListAction,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule\ListAction $scheduledListAction,
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeListAction = $realtimeListAction;
        $this->scheduledListAction = $scheduledListAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeListAction,
            );
        }

        return $this->createScheduleAction(
            $this->scheduledListAction,
        );
    }
}
