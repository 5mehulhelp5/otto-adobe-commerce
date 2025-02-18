<?php

declare(strict_types=1);

namespace M2E\Otto\Model\ControlPanel;

class CronTaskProvider implements \M2E\Core\Model\ControlPanel\Cron\TaskProviderInterface
{
    private \M2E\Otto\Model\Cron\TaskRepository $taskRepository;

    private array $tasks;

    public function __construct(\M2E\Otto\Model\Cron\TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getExtensionModuleName(): string
    {
        return \M2E\Otto\Model\ControlPanel\Extension::NAME;
    }

    public function getTasks(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->tasks)) {
            return $this->tasks;
        }

        $tasks = [];
        foreach ($this->taskRepository->getRegisteredGroups() as $group) {
            foreach ($this->taskRepository->getGroupTasks($group) as $groupTask) {
                $tasks[] = new \M2E\Core\Model\ControlPanel\CronTask(
                    $group,
                    $this->taskRepository->getNick($groupTask),
                    $groupTask,
                );
            }
        }

        return $this->tasks = $tasks;
    }
}
