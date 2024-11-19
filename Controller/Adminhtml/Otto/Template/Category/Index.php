<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Template\Category;

class Index extends \M2E\Otto\Controller\Adminhtml\Otto\Template\AbstractCategory
{
    public function execute()
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(__('Categories'));

        $content = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Otto\Template\Category::class
        );
        $this->addContent($content);

        return $this->getResultPage();
    }
}
