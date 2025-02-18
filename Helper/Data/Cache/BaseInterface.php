<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Data\Cache;

interface BaseInterface
{
    public function getValue(string $key);
    public function setValue(string $key, $value, array $tags = [], ?int $lifetime = null): void;
    public function removeValue(string $key): void;
    public function removeTagValues(string $tag): void;
    public function removeAllValues(): void;
}
