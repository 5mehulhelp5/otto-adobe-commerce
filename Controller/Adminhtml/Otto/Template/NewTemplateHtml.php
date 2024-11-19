<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Template;

class NewTemplateHtml extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractTemplate
{
    public function execute()
    {
        $nick = $this->getRequest()->getParam('nick');

        $this->setAjaxContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Otto\Listing\Template\NewTemplate\Form::class
            )
                 ->setData('nick', $nick)
        );

        return $this->getResult();
    }
}
