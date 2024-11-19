<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action\Type\ListAction;

class ProcessEnd extends \M2E\Otto\Model\Otto\Listing\Product\Action\Async\AbstractProcessEnd
{
    private \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Otto\Model\Otto\TagFactory $tagFactory;
    private ResponseFactory $responseFactory;

    public function __construct(
        \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Otto\Model\Otto\TagFactory $tagFactory,
        ResponseFactory $responseFactory
    ) {
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->responseFactory = $responseFactory;
    }

    protected function processComplete(array $resultData, array $messages): void
    {
        if (empty($resultData)) {
            $this->processFail($messages);

            return;
        }

        $this->processSuccess($resultData);
    }

    private function processSuccess(array $data): void
    {
        /** @var Response $responseObj */
        $responseObj = $this->responseFactory->create(
            $this->getListingProduct(),
            $this->getListingProduct()->getActionConfigurator(),
            $this->getLogBuffer(),
            $this->getParams(),
            $this->getStatusChanger(),
            $this->getRequestMetadata(),
            $data
        );

        $responseObj->process();
        $responseObj->generateResultMessage();
    }

    /**
     * @param \M2E\Otto\Model\Connector\Response\Message[] $messages
     *
     * @return void
     */
    private function processFail(array $messages): void
    {
        $tags = [];

        if (!empty($messages)) {
            $tags[] = $this->tagFactory->createWithHasErrorCode();
        }

        foreach ($messages as $message) {
            if (!$message->isSenderComponent() || empty($message->getCode())) {
                continue;
            }

            if ($message->isError()) {
                $tags[] = $this->tagFactory->createByErrorCode((string)$message->getCode(), $message->getText());
            }
        }

        if (!empty($tags)) {
            $this->tagBuffer->addTags($this->getListingProduct(), $tags);
            $this->tagBuffer->flush();
        }
    }
}
