<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Wizard\Step\BackHandler;

class Products implements \M2E\Otto\Model\Listing\Wizard\Step\BackHandlerInterface
{
    public function process(\M2E\Otto\Model\Listing\Wizard\Manager $manager): void
    {
        $manager->clearProducts();
    }
}
