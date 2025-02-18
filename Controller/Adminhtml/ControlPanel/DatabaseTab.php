<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\ControlPanel;

class DatabaseTab extends AbstractMain
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(\M2E\Core\Block\Adminhtml\ControlPanel\Tab\Database::class);
        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
