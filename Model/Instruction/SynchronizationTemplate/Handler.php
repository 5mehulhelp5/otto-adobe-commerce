<?php

namespace M2E\Otto\Model\Instruction\SynchronizationTemplate;

class Handler implements \M2E\Otto\Model\Instruction\Handler\HandlerInterface
{
    private \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\InputFactory $inputFactory;
    private \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\CheckerFactory $checkerFactory;
    private \M2E\Otto\Model\ScheduledAction\Repository $scheduledActionRepository;

    public function __construct(
        \M2E\Otto\Model\Instruction\SynchronizationTemplate\Checker\InputFactory $inputFactory,
        Checker\CheckerFactory $checkerFactory,
        \M2E\Otto\Model\ScheduledAction\Repository $scheduledActionRepository
    ) {
        $this->inputFactory = $inputFactory;
        $this->checkerFactory = $checkerFactory;
        $this->scheduledActionRepository = $scheduledActionRepository;
    }

    public function process(\M2E\Otto\Model\Instruction\Handler\Input $input): void
    {
        $scheduledAction = $this->scheduledActionRepository
            ->findByListingProductId($input->getListingProduct()->getId());

        $checkerInput = $this->inputFactory->create($input->getListingProduct(), $input->getInstructions());
        if ($scheduledAction !== null) {
            $checkerInput->setScheduledAction($scheduledAction);
        }

        foreach ($this->getAllCheckers() as $checkerClassName) {
            $checkerModel = $this->checkerFactory->create($checkerClassName, $checkerInput);

            if (!$checkerModel->isAllowed()) {
                continue;
            }

            $checkerModel->process();
        }
    }

    /**
     * @return string[]
     */
    private function getAllCheckers(): array
    {
        return [
            Checker\NotListedChecker::class,
            Checker\ActiveChecker::class,
            Checker\InactiveChecker::class,
        ];
    }
}
