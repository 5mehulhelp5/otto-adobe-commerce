<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Listing\Product\Action;

class TagManager
{
    private \M2E\Otto\Model\Otto\TagFactory $ottoTagFactory;
    private \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\Otto\Model\Tag\ValidatorIssues $validatorIssues;

    public function __construct(
        \M2E\Otto\Model\Otto\TagFactory $ottoTagFactory,
        \M2E\Otto\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\Otto\Model\Tag\ValidatorIssues $validatorIssues
    ) {
        $this->ottoTagFactory = $ottoTagFactory;
        $this->tagBuffer = $tagBuffer;
        $this->validatorIssues = $validatorIssues;
    }

    /**
     * @param \M2E\Otto\Model\Product $product
     * @param \M2E\Otto\Model\Otto\Listing\Product\Action\Validator\ValidatorMessage[] $messages
     */
    public function addErrorTags(\M2E\Otto\Model\Product $product, array $messages): void
    {
        if (empty($messages)) {
            return;
        }

        $tags = [];

        $userErrors = array_filter($messages, function ($message) {
            return $message->getCode() !== \M2E\Otto\Model\Tag\ValidatorIssues::NOT_USER_ERROR;
        });

        if (!empty($userErrors)) {
            foreach ($userErrors as $userError) {
                $error = $this->validatorIssues->mapByCode($userError->getCode());
                if ($error === null) {
                    continue;
                }

                $tags[] = $this->ottoTagFactory->createByErrorCode(
                    $error->getCode(),
                    $error->getText()
                );
            }

            $this->tagBuffer->addTags($product, $tags);
        }
    }
}
