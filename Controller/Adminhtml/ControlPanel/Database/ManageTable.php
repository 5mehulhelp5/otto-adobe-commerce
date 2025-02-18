<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Database;

class ManageTable extends AbstractTable
{
    protected \M2E\Otto\Helper\View\ControlPanel $controlPanelHelper;

    public function __construct(
        \M2E\Otto\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\Otto\Helper\Module $moduleHelper,
        \M2E\Core\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory
    ) {
        parent::__construct($moduleHelper, $databaseTableFactory);
        $this->controlPanelHelper = $controlPanelHelper;
    }

    public function execute()
    {
        $table = $this->getRequest()->getParam('table');

        if ($table === null) {
            return $this->_redirect($this->controlPanelHelper->getPageDatabaseTabUrl());
        }

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Core\Block\Adminhtml\ControlPanel\Tab\Database\Table::class,
                '',
                ['tableName' => $table],
            ),
        );

        return $this->getResultPage();
    }
}
