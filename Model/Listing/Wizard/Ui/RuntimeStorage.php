<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Wizard\Ui;

class RuntimeStorage
{
    private \M2E\Otto\Model\Listing\Wizard\Manager $manager;

    public function setManager(\M2E\Otto\Model\Listing\Wizard\Manager  $manager): void
    {
        $this->manager = $manager;
    }

    public function getManager(): \M2E\Otto\Model\Listing\Wizard\Manager
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->manager)) {
            throw new \LogicException('Listing wizard manager has not been set.');
        }

        return $this->manager;
    }
}
