<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

class RunRelistProducts extends \M2E\Otto\Controller\Adminhtml\Otto\Listing\AbstractAction
{
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime\RelistAction $realtimeRelistAction;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule\RelistAction $scheduledRelistAction;

    public function __construct(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime\RelistAction $realtimeRelistAction,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule\RelistAction $scheduledRelistAction,
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeRelistAction = $realtimeRelistAction;
        $this->scheduledRelistAction = $scheduledRelistAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeRelistAction,
            );
        }

        return $this->createScheduleAction(
            $this->scheduledRelistAction,
        );
    }
}
