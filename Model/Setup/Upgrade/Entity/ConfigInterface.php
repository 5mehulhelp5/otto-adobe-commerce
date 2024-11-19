<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Setup\Upgrade\Entity;

interface ConfigInterface
{
    /**
     * @return string[]
     */
    public function getFeaturesList(): array;
}
