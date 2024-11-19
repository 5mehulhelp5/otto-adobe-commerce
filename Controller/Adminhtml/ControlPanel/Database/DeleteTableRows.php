<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Database;

/**
 * Class \M2E\Otto\Controller\Adminhtml\ControlPanel\Database\DeleteTableRows
 */
class DeleteTableRows extends AbstractTable
{
    public function execute()
    {
        $ids = $this->prepareIds();
        $modelInstance = $this->getTableModel();

        if (empty($ids)) {
            $this->getMessageManager()->addError("Failed to get model or any of Table Rows are not selected.");
            $this->redirectToTablePage($modelInstance->getTableName());
        }

        $modelInstance->deleteEntries($ids);
        $this->afterTableAction($modelInstance->getTableName());
    }
}
