<?php

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Realtime;

use M2E\Otto\Model\Otto\Listing\Product\Action\Manual\Result;

abstract class AbstractRealtime extends \M2E\Otto\Model\Otto\Listing\Product\Action\Manual\AbstractManual
{
    private \M2E\Otto\Model\Otto\Listing\Product\Action\Dispatcher $actionDispatcher;

    public function __construct(
        \M2E\Otto\Model\Otto\Listing\Product\Action\Dispatcher $actionDispatcher,
        \M2E\Otto\Model\Product\ActionCalculator $calculator,
        \M2E\Otto\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct($calculator, $listingLogService);
        $this->actionDispatcher = $actionDispatcher;
    }

    protected function processAction(array $actions, array $params): Result
    {
        $params['logs_action_id'] = $this->getLogActionId();
        foreach ($actions as $action) {
            switch ($this->getAction()) {
                case \M2E\Otto\Model\Product::ACTION_LIST:
                    $result = $this->actionDispatcher->processList(
                        $action->getProduct(),
                        $params,
                        \M2E\Otto\Model\Product::STATUS_CHANGER_USER
                    );
                    break;
                case \M2E\Otto\Model\Product::ACTION_REVISE:
                    $result = $this->actionDispatcher->processRevise(
                        $action->getProduct(),
                        $params,
                        \M2E\Otto\Model\Product::STATUS_CHANGER_USER
                    );
                    break;
                case \M2E\Otto\Model\Product::ACTION_STOP:
                    $result = $this->actionDispatcher->processStop(
                        $action->getProduct(),
                        $params,
                        \M2E\Otto\Model\Product::STATUS_CHANGER_USER
                    );
                    break;
                case \M2E\Otto\Model\Product::ACTION_DELETE:
                    $result = $this->actionDispatcher->processDelete(
                        $action->getProduct(),
                        $params,
                        \M2E\Otto\Model\Product::STATUS_CHANGER_USER
                    );
                    break;
                case \M2E\Otto\Model\Product::ACTION_RELIST:
                    $result = $this->actionDispatcher->processRelist(
                        $action->getProduct(),
                        $params,
                        \M2E\Otto\Model\Product::STATUS_CHANGER_USER
                    );
                    break;

                default:
                    throw new \DomainException("Unknown action '{$this->getAction()}'");
            }
        }

        if ($result === \M2E\Otto\Helper\Data::STATUS_ERROR) {
            return Result::createError($this->getLogActionId());
        }

        if ($result === \M2E\Otto\Helper\Data::STATUS_WARNING) {
            return Result::createWarning($this->getLogActionId());
        }

        return Result::createSuccess($this->getLogActionId());
    }
}
