<?php

namespace M2E\Otto\Model\ScheduledAction;

use M2E\Otto\Model\ResourceModel\ScheduledAction\Collection as ScheduledActionCollection;
use M2E\Otto\Model\ResourceModel\ScheduledAction\CollectionFactory as ScheduledActionCollectionFactory;

class Processor
{
    private const LIST_PRIORITY = 25;
    private const RELIST_PRIORITY = 125;
    private const STOP_PRIORITY = 1000;
    private const REVISE_QTY_PRIORITY = 500;
    private const REVISE_PRICE_PRIORITY = 250;
    private const REVISE_TITLE_PRIORITY = 50;
    private const REVISE_DESCRIPTION_PRIORITY = 50;
    private const REVISE_IMAGES_PRIORITY = 50;
    private const REVISE_CATEGORIES_PRIORITY = 50;
    private const REVISE_PARTS_PRIORITY = 50;
    private const REVISE_PAYMENT_PRIORITY = 50;
    private const REVISE_SHIPPING_PRIORITY = 50;
    private const REVISE_RETURN_PRIORITY = 50;
    private const REVISE_OTHER_PRIORITY = 50;

    private \M2E\Otto\Model\Otto\Listing\Product\Action\ConfiguratorFactory $configuratorFactory;
    private \M2E\Otto\Model\Config\Manager $config;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private ScheduledActionCollectionFactory $scheduledActionCollectionFactory;
    private \M2E\Otto\Helper\Module\Exception $exceptionHelper;
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Dispatcher $actionDispatcher;
    /** @var \M2E\Otto\Model\ScheduledAction\Repository */
    private Repository $scheduledActionRepository;

    public function __construct(
        \M2E\Otto\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\Otto\Model\Otto\Listing\Product\Action\ConfiguratorFactory $configuratorFactory,
        \M2E\Otto\Model\Config\Manager $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        ScheduledActionCollectionFactory $scheduledActionCollectionFactory,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper,
        \M2E\Otto\Model\Otto\Listing\Product\Action\Dispatcher $actionDispatcher
    ) {
        $this->configuratorFactory = $configuratorFactory;
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
        $this->scheduledActionCollectionFactory = $scheduledActionCollectionFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->actionDispatcher = $actionDispatcher;
        $this->scheduledActionRepository = $scheduledActionRepository;
    }

    public function process(): void
    {
        $limit = $this->calculateActionsCountLimit();
        if ($limit === 0) {
            return;
        }

        $scheduledActions = $this->getScheduledActionsForProcessing($limit);
        if (empty($scheduledActions)) {
            return;
        }

        foreach ($scheduledActions as $scheduledAction) {
            try {
                $listingProduct = $scheduledAction->getListingProduct();
                $additionalData = $scheduledAction->getAdditionalData();
                $statusChanger = $scheduledAction->getStatusChanger();
            } catch (\M2E\Otto\Model\Exception\Logic $e) {
                if (!$e instanceof \M2E\Otto\Model\Exception\ListingProductNotFound) {
                    $this->exceptionHelper->process($e);
                }

                $this->scheduledActionRepository->remove($scheduledAction);

                continue;
            }

            $params = $additionalData['params'] ?? [];

            $listingProduct->setActionConfigurator($scheduledAction->getConfigurator());

            switch ($scheduledAction->getActionType()) {
                case \M2E\Otto\Model\Product::ACTION_LIST:
                    $this->actionDispatcher->processList($listingProduct, $params, $statusChanger);
                    break;
                case \M2E\Otto\Model\Product::ACTION_REVISE:
                    $this->actionDispatcher->processRevise($listingProduct, $params, $statusChanger);
                    break;
                case \M2E\Otto\Model\Product::ACTION_STOP:
                    $this->actionDispatcher->processStop($listingProduct, $params, $statusChanger);
                    break;
                case \M2E\Otto\Model\Product::ACTION_DELETE:
                    $this->actionDispatcher->processDelete($listingProduct, $params, $statusChanger);
                    break;
                case \M2E\Otto\Model\Product::ACTION_RELIST:
                    $this->actionDispatcher->processRelist($listingProduct, $params, $statusChanger);
                    break;
                default:
                    throw new \DomainException("Unknown action '{$scheduledAction->getActionType()}'");
            }

            $this->scheduledActionRepository->remove($scheduledAction);
        }
    }

    private function calculateActionsCountLimit(): int
    {
        $maxAllowedActionsCount = (int)$this->config->getGroupValue(
            '/listing/product/scheduled_actions/',
            'max_prepared_actions_count'
        );

        if ($maxAllowedActionsCount <= 0) {
            return 0;
        }

        return $maxAllowedActionsCount;
    }

    /**
     * @return \M2E\Otto\Model\ScheduledAction[]
     */
    private function getScheduledActionsForProcessing(int $limit): array
    {
        $connection = $this->resourceConnection->getConnection();

        $unionSelect = $connection->select()->union([
            $this->getListScheduledActionsPreparedCollection()->getSelect(),
            $this->getRelistScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseQtyScheduledActionsPreparedCollection()->getSelect(),
            $this->getRevisePriceScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseTitleScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseDescriptionScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseImagesScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseCategoriesScheduledActionsPreparedCollection()->getSelect(),
            $this->getRevisePartsScheduledActionsPreparedCollection()->getSelect(),
            $this->getRevisePaymentScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseShippingScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseReturnScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseOtherScheduledActionsPreparedCollection()->getSelect(),
            $this->getStopScheduledActionsPreparedCollection()->getSelect(),
            $this->getDeleteScheduledActionsPreparedCollection()->getSelect(),
        ]);

        $unionSelect->order(['coefficient DESC']);
        $unionSelect->order(['create_date ASC']);

        $unionSelect->distinct(true);
        $unionSelect->limit($limit);

        $scheduledActionsData = $unionSelect->query()->fetchAll();
        if (empty($scheduledActionsData)) {
            return [];
        }

        $scheduledActionsIds = [];
        foreach ($scheduledActionsData as $scheduledActionData) {
            $scheduledActionsIds[] = $scheduledActionData['id'];
        }

        return $this->scheduledActionRepository->getByIds($scheduledActionsIds);
    }

    // ---------------------------------------

    private function getListScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory->create()->getScheduledActionsPreparedCollection(
            self::LIST_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_LIST
        );
    }

    private function getRelistScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::RELIST_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_RELIST
        );

        return $collection;
    }

    private function getReviseQtyScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_QTY_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('qty');

        return $collection;
    }

    private function getRevisePriceScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_PRICE_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('price');

        return $collection;
    }

    private function getReviseTitleScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_TITLE_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('title');

        return $collection;
    }

    private function getReviseDescriptionScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_DESCRIPTION_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('description');

        return $collection;
    }

    private function getReviseImagesScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_IMAGES_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('images');

        return $collection;
    }

    private function getReviseCategoriesScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_CATEGORIES_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('categories');

        return $collection;
    }

    private function getRevisePartsScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_PARTS_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('parts');

        return $collection;
    }

    private function getRevisePaymentScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_PAYMENT_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('payment');

        return $collection;
    }

    private function getReviseShippingScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_SHIPPING_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('shipping');

        return $collection;
    }

    private function getReviseReturnScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_RETURN_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('return');

        return $collection;
    }

    private function getReviseOtherScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_OTHER_PRIORITY,
            \M2E\Otto\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter('other');

        return $collection;
    }

    private function getStopScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory
            ->create()
            ->getScheduledActionsPreparedCollection(
                self::STOP_PRIORITY,
                \M2E\Otto\Model\Product::ACTION_STOP
            );
    }

    private function getDeleteScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory
            ->create()
            ->getScheduledActionsPreparedCollection(
                self::STOP_PRIORITY,
                \M2E\Otto\Model\Product::ACTION_DELETE
            );
    }
}
