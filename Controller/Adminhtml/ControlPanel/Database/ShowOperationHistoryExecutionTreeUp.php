<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Database;

class ShowOperationHistoryExecutionTreeUp extends AbstractTable
{
    private \M2E\Otto\Model\OperationHistory\Repository $repository;

    public function __construct(
        \M2E\Otto\Model\OperationHistory\Repository $repository,
        \M2E\Otto\Helper\Module $moduleHelper,
        \M2E\Core\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory
    ) {
        parent::__construct($moduleHelper, $databaseTableFactory);
        $this->repository = $repository;
    }

    public function execute()
    {
        $operationHistoryId = $this->getRequest()->getParam('operation_history_id');
        if (empty($operationHistoryId)) {
            $this->getMessageManager()->addErrorMessage('Operation history ID is not presented.');

            $this->redirectToTablePage(
                \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_OPERATION_HISTORY
            );

            return;
        }

        $operationHistory = $this->repository->get((int)$operationHistoryId);

        $this->getResponse()->setBody(
            '<pre>' . $operationHistory->getExecutionTreeUpInfo() . '</pre>',
        );
    }
}
