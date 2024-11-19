<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\Images;

class Value
{
    public ?\M2E\Otto\Model\Product\DataProvider\Images\Image $mainImage;
    /** @var \M2E\Otto\Model\Product\DataProvider\Images\Image[] */
    public array $set;
    public string $imagesHash;

    public function __construct(
        ?\M2E\Otto\Model\Product\DataProvider\Images\Image $mainImage,
        array $set,
        string $imagesHash
    ) {
        $this->mainImage = $mainImage;
        $this->set = $set;
        $this->imagesHash = $imagesHash;
    }
}
