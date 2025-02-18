<?php

declare(strict_types=1);

namespace M2E\Otto\Setup;

class MagentoCoreConfigSettings implements \M2E\Core\Model\Setup\MagentoCoreConfigSettingsInterface
{
    public function getConfigKeyPrefix(): string
    {
        return 'm2e_otto';
    }
}
