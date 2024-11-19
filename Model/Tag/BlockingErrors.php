<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Tag;

class BlockingErrors
{
    public function getList(): array
    {
        return [
            'error-without-reason', // Product was not Listed on the channel. Please refer to the Product’s previous Listing logs for the specific error reason
        ];
    }
}
