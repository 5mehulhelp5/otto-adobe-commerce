<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Select;

class Errors implements \Magento\Framework\Data\OptionSourceInterface
{
    private \M2E\Otto\Model\Tag\Repository $repository;

    public function __construct(\M2E\Otto\Model\Tag\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->repository->getAllTags() as $tag) {
            if ($tag->getErrorCode() === \M2E\Otto\Model\Tag::HAS_ERROR_ERROR_CODE) {
                continue;
            }

            $options[] = [
                'label' => substr($tag->getText(), 0, 35),
                'value' => $tag->getErrorCode(),
            ];
        }

        return $options;
    }
}
