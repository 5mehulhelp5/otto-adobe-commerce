<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Database;

class ManageTable extends AbstractTable
{
    protected \M2E\Otto\Helper\View\ControlPanel $controlPanelHelper;

    public function __construct(
        \M2E\Otto\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\Otto\Helper\Module $moduleHelper,
        \M2E\Otto\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \M2E\Otto\Model\Module $module
    ) {
        parent::__construct($moduleHelper, $databaseTableFactory, $module);
        $this->controlPanelHelper = $controlPanelHelper;
    }

    public function execute()
    {
        $this->init();
        $table = $this->getRequest()->getParam('table');

        if ($table === null) {
            return $this->_redirect($this->controlPanelHelper->getPageDatabaseTabUrl());
        }

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\ControlPanel\Tabs\Database\Table::class,
                '',
                ['tableName' => $table],
            ),
        );

        return $this->getResultPage();
    }
}
