<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Schedule;

class StopAndRemoveAction extends AbstractSchedule
{
    private \M2E\Otto\Model\Product\RemoveHandler $removeHandler;

    public function __construct(
        \M2E\Otto\Model\Product\RemoveHandler $removeHandler,
        \M2E\Otto\Model\ScheduledAction\CreateService $scheduledActionCreateService,
        \M2E\Otto\Model\Product\ActionCalculator $calculator,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct($scheduledActionCreateService, $calculator, $listingLogService);
        $this->removeHandler = $removeHandler;
    }

    protected function getAction(): int
    {
        return \M2E\Otto\Model\Product::ACTION_DELETE;
    }

    protected function prepareOrFilterProducts(array $listingsProducts): array
    {
        $result = [];
        foreach ($listingsProducts as $listingProduct) {
            if ($listingProduct->isStoppable()) {
                $result[] = $listingProduct;

                continue;
            }

            $this->removeHandler->process($listingProduct);
        }

        return $result;
    }

    protected function calculateAction(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Product\ActionCalculator $calculator
    ): \M2E\Otto\Model\Product\Action {
        return \M2E\Otto\Model\Product\Action::createStop($product);
    }

    protected function logAboutSkipAction(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Listing\LogService $logService
    ): void {
    }
}
