<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Note;

use M2E\Otto\Model\Order\Note\MagentoOrderUpdateTrait;

class Create
{
    use MagentoOrderUpdateTrait;

    private \M2E\Otto\Model\Order\Note\Repository $repository;
    private \M2E\Otto\Model\Order\NoteFactory $noteFactory;

    public function __construct(
        \M2E\Otto\Model\Order\Note\Repository $repository,
        \M2E\Otto\Model\Order\NoteFactory $noteFactory,
        \M2E\Otto\Model\Magento\Order\Updater $magentoOrderUpdater
    ) {
        $this->repository = $repository;
        $this->noteFactory = $noteFactory;
        $this->magentoOrderUpdater = $magentoOrderUpdater;
    }

    public function createCustomNote(\M2E\Otto\Model\Order $order, string $note): \M2E\Otto\Model\Order\Note
    {
        $obj = $this->create($order, $note);

        $comment = (string)__(
            'Custom Note was added to the corresponding Otto order: %note.',
            ['note' => $obj->getNote()],
        );
        $this->updateMagentoOrderComment($order, $comment);

        return $obj;
    }

    public function create(\M2E\Otto\Model\Order $order, string $note): \M2E\Otto\Model\Order\Note
    {
        $obj = $this->noteFactory->create();
        $obj->init($order->getId(), $note);

        $this->repository->create($obj);

        return $obj;
    }
}
