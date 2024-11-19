<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Servicing\Task;

class Settings implements \M2E\Otto\Model\Servicing\TaskInterface
{
    public const NAME = 'settings';

    private \M2E\Otto\Model\Module $module;

    public function __construct(
        \M2E\Otto\Model\Module $module
    ) {
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getServerTaskName(): string
    {
        return self::NAME;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function getRequestData(): array
    {
        return [];
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function processResponseData(array $data): void
    {
        $this->updateLastVersion($data);
    }

    /**
     * @param array $data
     *
     * @return void
     */
    private function updateLastVersion(array $data): void
    {
        if (empty($data['last_version'])) {
            return;
        }

        $this->module->setLatestVersion((string)$data['last_version']);
    }
}
