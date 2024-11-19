<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\ListAction;

class ResponseFactory extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractResponseFactory
{
    protected function getResponseClass(): string
    {
        return Response::class;
    }
}
