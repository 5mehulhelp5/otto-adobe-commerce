<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime;

class ReviseAction extends AbstractRealtime
{
    use \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\Otto\Model\Product::ACTION_REVISE;
    }

    protected function calculateAction(\M2E\Otto\Model\Product $product, \M2E\Otto\Model\Product\ActionCalculator $calculator): \M2E\Otto\Model\Product\Action
    {
        $result = $calculator->calculateToReviseOrStop($product, true, true, true, true, true);
        if ($result->isActionStop()) {
            return \M2E\Otto\Model\Product\Action::createNothing($product);
        }

        return $result;
    }

    protected function logAboutSkipAction(
        \M2E\Otto\Model\Product $product,
        \M2E\Otto\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Otto\Helper\Data::INITIATOR_USER,
            \M2E\Otto\Model\Listing\Log::ACTION_REVISE_PRODUCT,
            $this->getLogActionId(),
            $this->createManualSkipReviseMessage(),
            \M2E\Otto\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
