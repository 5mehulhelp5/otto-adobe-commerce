<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ControlPanel\Inspection;

class Processor
{
    private \M2E\Otto\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory;

    private \M2E\Otto\Model\ControlPanel\Inspection\Result\Factory $resultFactory;

    public function __construct(
        \M2E\Otto\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory,
        \M2E\Otto\Model\ControlPanel\Inspection\Result\Factory $resultFactory
    ) {
        $this->handlerFactory = $handlerFactory;
        $this->resultFactory = $resultFactory;
    }

    public function process(\M2E\Otto\Model\ControlPanel\Inspection\Definition $definition)
    {
        $handler = $this->handlerFactory->create($definition);

        try {
            $issues = $handler->process();
            $result = $this->resultFactory->createSuccess($issues);
        } catch (\Exception $e) {
            $result = $this->resultFactory->createFailed($e->getMessage());
        }

        return $result;
    }
}
