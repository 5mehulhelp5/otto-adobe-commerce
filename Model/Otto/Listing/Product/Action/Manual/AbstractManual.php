<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Manual;

abstract class AbstractManual
{
    private int $logsActionId;
    private \M2E\Otto\Model\Product\ActionCalculator $calculator;
    private \M2E\Otto\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\Otto\Model\Product\ActionCalculator $calculator,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        $this->calculator = $calculator;
        $this->listingLogService = $listingLogService;
    }

    abstract protected function getAction(): int;

    /**
     * @param \M2E\Otto\Model\Product[] $listingsProducts
     *
     * @return array
     */
    protected function prepareOrFilterProducts(array $listingsProducts): array
    {
        return $listingsProducts;
    }

    /**
     * @param \M2E\Otto\Model\Product[] $listingsProducts
     * @param array $params
     * @param int $logsActionId
     *
     * @return Result
     */
    public function process(array $listingsProducts, array $params, int $logsActionId): Result
    {
        $this->logsActionId = $logsActionId;

        if (empty($listingsProducts)) {
            return Result::createError($this->getLogActionId());
        }

        $listingsProducts = $this->prepareOrFilterProducts($listingsProducts);
        if (empty($listingsProducts)) {
            return Result::createSuccess($this->getLogActionId());
        }

        $actions = $this->calculateActions($listingsProducts);
        if (empty($actions)) {
            return Result::createSuccess($this->getLogActionId());
        }

        return $this->processAction($actions, $params);
    }

    private function calculateActions(array $products): array
    {
        $result = [];
        foreach ($products as $product) {
            $calculateAction = $this->calculateAction($product, $this->calculator);
            if ($calculateAction->isActionNothing()) {
                $this->logAboutSkipAction($product, $this->listingLogService);

                continue;
            }

            $result[] = $calculateAction;
        }

        return $result;
    }

    abstract protected function calculateAction(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Product\ActionCalculator $calculator
    ): \M2E\Otto\Model\Product\Action;

    abstract protected function logAboutSkipAction(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Listing\LogService $logService
    ): void;

    /**
     * @param \M2E\Otto\Model\Product\Action[] $actions
     * @param array $params
     *
     * @return Result
     */
    abstract protected function processAction(array $actions, array $params): Result;

    protected function getLogActionId(): int
    {
        return $this->logsActionId;
    }
}
