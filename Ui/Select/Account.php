<?php

declare(strict_types=1);

namespace M2E\Otto\Ui\Select;

class Account implements \Magento\Framework\Data\OptionSourceInterface
{
    private \M2E\Otto\Model\Account\Repository $repository;

    public function __construct(\M2E\Otto\Model\Account\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->repository->getAll() as $account) {
            $options[] = [
                'label' => $account->getTitle(),
                'value' => $account->getId(),
            ];
        }

        return $options;
    }
}
