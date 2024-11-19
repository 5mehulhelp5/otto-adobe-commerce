<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\Stop;

class Response extends \M2E\Otto\Model\Otto\Listing\Product\Action\Type\AbstractResponse
{
    private \M2E\Otto\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $productRepository,
        \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Otto\Model\Otto\TagFactory $tagFactory
    ) {
        parent::__construct($tagBuffer, $tagFactory);

        $this->productRepository = $productRepository;
    }

    public function process(): void
    {
        if (!$this->isSuccess()) {
            $this->processFail();

            return;
        }

        $this->processSuccess();
    }

    private function isSuccess(): bool
    {
        $responseData = $this->getResponseData();

        return !empty($responseData['products'][0]['is_success']);
    }

    public function processSuccess(): void
    {
        $this->getProduct()->setStatusInactive($this->getStatusChanger());

        $this->productRepository->save($this->getProduct());
    }

    public function processFail()
    {
        $responseData = $this->getResponseData();
        foreach ($responseData['products'][0]['messages'] as $message) {
            $this->getLogBuffer()->addFail($message['title']);
        }

        $this->addTags($responseData['products'][0]['messages']);
    }

    public function generateResultMessage(): void
    {
        if (!$this->isSuccess()) {
            $this->getLogBuffer()->addFail('Product failed to be stopped.');

            return;
        }

        $this->getLogBuffer()->addSuccess('Item was Stopped');
    }
}
