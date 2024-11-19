<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Note;

class Repository
{
    private \M2E\Otto\Model\ResourceModel\Order\Note $noteResource;
    private \M2E\Otto\Model\Order\NoteFactory $noteFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Order\Note $noteResource,
        \M2E\Otto\Model\Order\NoteFactory $noteFactory
    ) {
        $this->noteResource = $noteResource;
        $this->noteFactory = $noteFactory;
    }

    public function create(\M2E\Otto\Model\Order\Note $note): void
    {
        $this->noteResource->save($note);
    }

    public function save(\M2E\Otto\Model\Order\Note $note): void
    {
        $this->noteResource->save($note);
    }

    public function remove(\M2E\Otto\Model\Order\Note  $note): void
    {
        $this->noteResource->delete($note);
    }

    public function get(int $id): \M2E\Otto\Model\Order\Note
    {
        $note = $this->find($id);
        if ($note === null) {
            throw new \M2E\Otto\Model\Exception\Logic("Order Note $id not found.");
        }

        return $note;
    }

    public function find(int $id): ?\M2E\Otto\Model\Order\Note
    {
        $note = $this->noteFactory->create();
        $this->noteResource->load($note, $id);

        if ($note->isObjectNew()) {
            return null;
        }

        return $note;
    }
}
