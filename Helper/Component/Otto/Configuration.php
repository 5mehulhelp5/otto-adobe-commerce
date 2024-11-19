<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Component\Otto;

class Configuration
{
    public const IDENTIFIER_CODE_MODE_NOT_SET = 0;
    public const IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE = 1;

    public const CONFIG_GROUP = '/otto/configuration/';

    private \M2E\Otto\Model\Config\Manager $config;

    public function __construct(\M2E\Otto\Model\Config\Manager $config)
    {
        $this->config = $config;
    }

    public function getIdentifierCodeMode(): int
    {
        return (int)$this->config->getGroupValue(self::CONFIG_GROUP, 'identifier_code_mode');
    }

    public function getIdentifierCodeCustomAttribute()
    {
        return $this->config->getGroupValue(self::CONFIG_GROUP, 'identifier_code_custom_attribute');
    }

    public function isIdentifierCodeModeCustomAttribute(): bool
    {
        return $this->getIdentifierCodeMode() == self::IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function setConfigValues(array $values): void
    {
        if (isset($values['identifier_code_mode'])) {
            $this->config->setGroupValue(
                self::CONFIG_GROUP,
                'identifier_code_mode',
                $values['identifier_code_mode']
            );
        }

        if (isset($values['identifier_code_custom_attribute'])) {
            $this->config->setGroupValue(
                self::CONFIG_GROUP,
                'identifier_code_custom_attribute',
                $values['identifier_code_custom_attribute']
            );
        }
    }
}
