<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Select;

class Category implements \Magento\Framework\Data\OptionSourceInterface
{
    private \M2E\Otto\Model\Category\Repository $repository;

    public function __construct(\M2E\Otto\Model\Category\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->repository->getAll() as $category) {
            $options[] = [
                'label' => $category->getTitle(),
                'value' => $category->getId(),
            ];
        }

        return $options;
    }
}
