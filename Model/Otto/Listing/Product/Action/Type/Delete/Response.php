<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Delete;

class Response extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractResponse
{
    private \M2E\Otto\Model\Product\RemoveHandler $removeHandlerFactory;

    public function __construct(
        \M2E\Otto\Model\Product\RemoveHandler $removeHandler,
        \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Otto\Model\Otto\TagFactory $tagFactory
    ) {
        parent::__construct($tagBuffer, $tagFactory);

        $this->removeHandlerFactory = $removeHandler;
    }

    public function process(): void
    {
        $listingProduct = $this->getProduct();
        $this->removeHandlerFactory->process($listingProduct);
    }

    public function generateResultMessage(): void
    {
        $this->getLogBuffer()->addSuccess('Item was Removed');
    }
}
