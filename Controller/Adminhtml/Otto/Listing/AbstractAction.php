<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing;

abstract class AbstractAction extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractMain
{
    private \M2E\Otto\Model\Listing\LogService $listingLogService;
    private \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct();

        $this->listingLogService = $listingLogService;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    protected function isRealtimeProcess(): bool
    {
        return $this->getRequest()->getParam('is_realtime') === 'true';
    }

    protected function processRealtime(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime\AbstractRealtime $processor,
        array $params = []
    ) {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return $this->setRawContent('You should select Products');
        }

        $logsActionId = $this->createLogsActionId();
        $listingsProducts = $this->loadProducts($listingsProductsIds);

        if (empty($listingsProducts)) {
            $this->setJsonContent(['result' => 'error', 'action_id' => $logsActionId]);

            return $this->getResult();
        }

        $result = $processor->process($listingsProducts, $params, $logsActionId);

        if ($result->isError()) {
            $this->setJsonContent(['result' => 'error', 'action_id' => $logsActionId]);

            return $this->getResult();
        }

        if ($result->isWarning()) {
            $this->setJsonContent(['result' => 'warning', 'action_id' => $logsActionId]);

            return $this->getResult();
        }

        $this->setJsonContent(['result' => 'success', 'action_id' => $logsActionId]);

        return $this->getResult();
    }

    protected function createScheduleAction(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule\AbstractSchedule $processor,
        array $params = []
    ) {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return $this->setRawContent('You should select Products');
        }

        $logsActionId = $this->createLogsActionId();
        $listingsProducts = $this->loadProducts($listingsProductsIds);

        if (empty($listingsProducts)) {
            $this->setJsonContent(['result' => 'error', 'action_id' => $logsActionId]);

            return $this->getResult();
        }

        $processor->process($listingsProducts, $params, $logsActionId);

        $this->setJsonContent(['result' => 'success', 'action_id' => $logsActionId]);

        return $this->getResult();
    }

    private function createLogsActionId(): int
    {
        return $this->listingLogService->getNextActionId();
    }

    /**
     * @param string $listingsProductsIds
     *
     * @return \M2E\Otto\Model\Product[]
     */
    private function loadProducts(string $listingsProductsIds): array
    {
        $productsCollection = $this->listingProductCollectionFactory->create();
        $productsCollection->addFieldToFilter('id', explode(',', $listingsProductsIds));

        return $productsCollection->getItems();
    }
}
