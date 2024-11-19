<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel\Database;

/**
 * Class \M2E\Otto\Controller\Adminhtml\ControlPanel\Database\GetTableCellsPopupHtml
 */
class GetTableCellsPopupHtml extends AbstractTable
{
    public function execute()
    {
        $block = $this->getLayout()
                      ->createBlock(
                          \M2E\Otto\Block\Adminhtml\ControlPanel\Tabs\Database\Table\TableCellsPopup::class
                      );
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
