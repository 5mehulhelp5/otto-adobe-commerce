<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Magento\Product\Image;

class Set
{
    /** @var array<string, \M2E\Otto\Model\Magento\Product\Image> */
    private array $imagesByHash = [];

    public function add(\M2E\Otto\Model\Magento\Product\Image $image): void
    {
        if (empty($this->imagesByHash[$image->getHash()])) {
            $this->imagesByHash[$image->getHash()] = $image;
        }
    }

    public function find(string $hash): ?\M2E\Otto\Model\Magento\Product\Image
    {
        return $this->imagesByHash[$hash] ?? null;
    }

    /**
     * @return \M2E\Otto\Model\Magento\Product\Image[]
     */
    public function getAll(): array
    {
        return array_values($this->imagesByHash);
    }
}
