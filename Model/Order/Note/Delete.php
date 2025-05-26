<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Note;

use M2E\Otto\Model\Order\Note\MagentoOrderUpdateTrait;

class Delete
{
    use MagentoOrderUpdateTrait;

    private \M2E\Otto\Model\Order\Note\Repository $repository;
    private \M2E\Otto\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\Otto\Model\Order\Note\Repository $repository,
        \M2E\Otto\Model\Order\Repository $orderRepository,
        \M2E\Otto\Model\Magento\Order\Updater $magentoOrderUpdater
    ) {
        $this->repository = $repository;
        $this->orderRepository = $orderRepository;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
    }

    public function process(\M2E\Otto\Model\Order\Note $note): void
    {
        $order = $this->orderRepository->get($note->getOrderId());

        $this->repository->remove($note);

        $this->updateMagentoOrderComment(
            $order,
            (string)__(
                'Custom Note for the corresponding %channel_title order was deleted.',
                [
                    'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                ],
            ),
        );
    }
}
