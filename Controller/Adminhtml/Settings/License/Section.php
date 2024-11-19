<?php

namespace M2E\Otto\Controller\Adminhtml\Settings\License;

class Section extends \M2E\Otto\Controller\Adminhtml\AbstractBase
{
    public function execute()
    {
        $content = $this->getLayout()
                        ->createBlock(\M2E\Otto\Block\Adminhtml\System\Config\Sections\License::class);
        $this->setAjaxContent($content);

        return $this->getResult();
    }
}
