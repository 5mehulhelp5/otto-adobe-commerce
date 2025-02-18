<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Data\Cache;

class Runtime implements BaseInterface
{
    private array $cacheStorage = [];

    public function getValue($key)
    {
        return $this->cacheStorage[$key]['data'] ?? null;
    }

    public function setValue($key, $value, array $tags = [], $lifetime = null): void
    {
        $this->cacheStorage[$key] = [
            'data' => $value,
            'tags' => $tags,
        ];
    }

    public function removeValue($key): void
    {
        unset($this->cacheStorage[$key]);
    }

    public function removeTagValues($tag): void
    {
        foreach ($this->cacheStorage as $key => $data) {
            if (!in_array($tag, $data['tags'])) {
                continue;
            }

            unset($this->cacheStorage[$key]);
        }
    }

    public function removeAllValues(): void
    {
        $this->cacheStorage = [];
    }
}
