<?php

namespace M2E\Otto\Model\Cron\Runner;

use M2E\Otto\Model\Cron\AbstractRunner;

class Developer extends AbstractRunner
{
    private array $allowedTasks;
    private \M2E\Otto\Model\Cron\TaskRepository $taskRepository;

    public function __construct(
        \M2E\Otto\Model\Cron\TaskRepository $taskRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\Otto\Model\Lock\Transactional\ManagerFactory $lockTransactionManagerFactory,
        \M2E\Otto\Helper\Module\Exception $exceptionHelper,
        \M2E\Otto\Helper\Magento $magentoHelper,
        \M2E\Otto\Model\Config\Manager $config,
        \M2E\Otto\Helper\Module $moduleHelper,
        \M2E\Otto\Helper\Module\Maintenance $maintenanceHelper,
        \M2E\Otto\Helper\Module\Cron $cronHelper,
        \M2E\Otto\Model\Cron\OperationHistoryFactory $operationHistoryFactory,
        \M2E\Otto\Helper\Client\MemoryLimit $memoryLimit,
        \M2E\Otto\Model\Cron\Strategy $strategy
    ) {
        parent::__construct(
            $storeManager,
            $lockTransactionManagerFactory,
            $exceptionHelper,
            $magentoHelper,
            $config,
            $moduleHelper,
            $maintenanceHelper,
            $cronHelper,
            $operationHistoryFactory,
            $memoryLimit,
            $strategy,
        );

        $this->taskRepository = $taskRepository;
    }

    public function getNick(): ?string
    {
        return null;
    }

    public function getInitiator(): int
    {
        return \M2E\Otto\Helper\Data::INITIATOR_DEVELOPER;
    }

    public function process(): void
    {
        // @codingStandardsIgnoreLine
        session_write_close();
        parent::process();
    }

    protected function getStrategy(): \M2E\Otto\Model\Cron\Strategy
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->allowedTasks)) {
            $this->allowedTasks = $this->taskRepository->getRegisteredTasks();
        }

        $strategy = parent::getStrategy();
        $strategy->setAllowedTasks($this->allowedTasks);

        return $strategy;
    }

    /**
     * @param array $tasks
     *
     * @return $this
     */
    public function setAllowedTasks(array $tasks): self
    {
        $this->allowedTasks = $tasks;

        return $this;
    }

    protected function isPossibleToRun(): bool
    {
        return true;
    }

    protected function canProcessRunner(): bool
    {
        return true;
    }

    protected function setLastRun(): void
    {
    }

    protected function setLastAccess(): void
    {
    }
}
