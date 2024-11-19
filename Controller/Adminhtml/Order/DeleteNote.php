<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Order;

class DeleteNote extends \M2E\Otto\Controller\Adminhtml\AbstractOrder
{
    private \M2E\Otto\Model\Order\Note\Delete $deleteService;
    private \M2E\Otto\Model\Order\Note\Repository $repository;

    public function __construct(
        \M2E\Otto\Model\Order\Note\Repository $repository,
        \M2E\Otto\Model\Order\Note\Delete $deleteService
    ) {
        parent::__construct();
        $this->deleteService = $deleteService;
        $this->repository = $repository;
    }

    public function execute()
    {
        $noteId = $this->getRequest()->getParam('note_id');
        if ($noteId === null) {
            $this->setJsonContent(['result' => false]);

            return $this->getResult();
        }

        $note = $this->repository->get((int)$noteId);
        $this->deleteService->process($note);

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }
}
