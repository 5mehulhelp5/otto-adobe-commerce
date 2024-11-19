<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Product\DataProvider\Images;

class Image
{
    public string $type;
    public string $location;

    public function __construct(
        string $type,
        string $location
    ) {
        $this->type = $type;
        $this->location = $location;
    }
}
