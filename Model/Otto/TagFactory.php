<?php

namespace M2E\Otto\Model\Otto;

class TagFactory
{
    /** @var \M2E\Otto\Model\TagFactory */
    private $tagFactory;

    public function __construct(\M2E\Otto\Model\TagFactory $tagFactory)
    {
        $this->tagFactory = $tagFactory;
    }

    public function createByErrorCode(string $errorCode, string $text): \M2E\Otto\Model\Tag
    {
        $text = $this->getPreparedText($errorCode) ?? $this->trimText($text);

        return $this->tagFactory->create($errorCode, $text);
    }

    public function createWithHasErrorCode(): \M2E\Otto\Model\Tag
    {
        return $this->tagFactory->create(\M2E\Otto\Model\Tag::HAS_ERROR_ERROR_CODE, 'Has error');
    }

    private function getPreparedText(string $errorCode): ?string
    {
        return null;
    }

    private function trimText(string $text): string
    {
        if (strlen($text) <= 255) {
            return $text;
        }

        return substr($text, 0, 252) . '...';
    }
}
