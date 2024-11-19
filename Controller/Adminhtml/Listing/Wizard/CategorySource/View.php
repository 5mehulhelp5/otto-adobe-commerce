<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard\CategorySource;

use M2E\Otto\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\Otto\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY_MODE;
    }

    protected function process(\M2E\Otto\Model\Listing $listing)
    {
        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Listing\Wizard\CategorySelectMode::class,
            ),
        );

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Set Your Categories'));

        $this->setPageHelpLink('https://docs-m2.m2epro.com/set-category-for-otto-items');

        return $this->getResult();
    }
}
