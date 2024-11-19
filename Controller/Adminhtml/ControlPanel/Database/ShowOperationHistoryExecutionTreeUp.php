<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Database;

class ShowOperationHistoryExecutionTreeUp extends AbstractTable
{
    private \M2E\Otto\Model\OperationHistory\Repository $repository;

    public function __construct(
        \M2E\Otto\Model\OperationHistory\Repository $repository,
        \M2E\Otto\Helper\Module $moduleHelper,
        \M2E\Otto\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \M2E\Otto\Model\Module $module
    ) {
        parent::__construct($moduleHelper, $databaseTableFactory, $module);
        $this->repository = $repository;
    }

    public function execute()
    {
        $operationHistoryId = $this->getRequest()->getParam('operation_history_id');
        if (empty($operationHistoryId)) {
            $this->getMessageManager()->addErrorMessage('Operation history ID is not presented.');

            $this->redirectToTablePage(
                \M2E\Otto\Helper\Module\Database\Tables::TABLE_NAME_OPERATION_HISTORY,
            );

            //exit
        }

        $operationHistory = $this->repository->get((int)$operationHistoryId);

        $this->getResponse()->setBody(
            '<pre>' . $operationHistory->getExecutionTreeUpInfo() . '</pre>',
        );
    }
}
