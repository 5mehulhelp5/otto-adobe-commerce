<?php

namespace M2E\Otto\Controller\Adminhtml\Maintenance;

class Index extends \Magento\Backend\App\Action
{
    /** @var \M2E\Otto\Helper\Module\Maintenance */
    private $moduleMaintenanceHelper;
    /** @var \Magento\Framework\View\Result\PageFactory */
    private $pageFactory;

    public function __construct(
        \M2E\Otto\Helper\Module\Maintenance $moduleMaintenanceHelper,
        \M2E\Otto\Helper\Module\Wizard $wizardHelper,
        \M2E\Otto\Controller\Adminhtml\Context $controllerContext,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);

        $this->pageFactory = $controllerContext->getResultPageFactory();
        $this->moduleMaintenanceHelper = $moduleMaintenanceHelper;
    }

    public function execute()
    {
        if (!$this->moduleMaintenanceHelper->isEnabled()) {
            return $this->_redirect('admin');
        }

        $result = $this->pageFactory->create();

        $result->getConfig()->getTitle()->set(__('M2E Otto is currently under maintenance'));
        $this->_setActiveMenu('M2E_Otto::otto_maintenance');

        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $result->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $block->setData(
            'is_maintenance_due_low_magento_version',
            $this->moduleMaintenanceHelper->isEnabledDueLowMagentoVersion()
        );

        $block->setTemplate('M2E_Otto::maintenance.phtml');

        $this->_addContent($block);

        return $result;
    }
}
