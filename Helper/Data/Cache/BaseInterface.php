<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Data\Cache;

interface BaseInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getValue(string $key);

    /**
     * @param string $key
     * @param mixed $value
     * @param array $tags
     * @param int|null $lifetime
     *
     * @return void
     */
    public function setValue(string $key, $value, array $tags = [], $lifetime = null): void;

    /**
     * @param string $key
     *
     * @return void
     */
    public function removeValue(string $key): void;

    /**
     * @param string $tag
     *
     * @return void
     */
    public function removeTagValues(string $tag): void;

    /**
     * @return void
     */
    public function removeAllValues(): void;
}
