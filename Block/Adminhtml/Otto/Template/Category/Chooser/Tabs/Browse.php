<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Template\Category\Chooser\Tabs;

class Browse extends \M2E\Otto\Block\Adminhtml\Magento\AbstractBlock
{
    public \M2E\Otto\Helper\View\Otto $viewHelper;
    private \M2E\Otto\Helper\Module\Wizard $wizardHelper;

    public function __construct(
        \M2E\Otto\Helper\View\Otto $viewHelper,
        \M2E\Otto\Helper\Module\Wizard $wizardHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewHelper = $viewHelper;
        $this->wizardHelper = $wizardHelper;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoCategoryChooserCategoryBrowse');
        $this->setTemplate('otto/template/category/chooser/tabs/browse.phtml');
    }

    public function isWizardActive()
    {
        return $this->wizardHelper->isActive(\M2E\Otto\Helper\View\Otto::WIZARD_INSTALLATION_NICK);
    }
}
