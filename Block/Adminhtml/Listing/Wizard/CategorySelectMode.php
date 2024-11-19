<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Listing\Wizard;

class CategorySelectMode extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer
{
    use WizardTrait;

    public const MODE_SAME = 'same';
    public const MODE_MANUALLY = 'manually';

    private \M2E\Otto\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        \M2E\Otto\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingCategoryMode');
        $this->_controller = 'adminhtml_listing_wizard_category';
        $this->_mode = 'modeSame';

        $this->_headerText = __('Set Category');

        $urlSubmit = $this->getUrl(
            '*/listing_wizard_categorySource/complete',
            ['id' => $this->uiWizardRuntimeStorage->getManager()->getWizardId()],
        );

        $this->prepareButtons(
            [
                'label' => __('Continue'),
                'class' => 'action-primary forward',
                'onclick' => 'CommonObj.submitForm(\'' . $urlSubmit . '\');',
            ],
            $this->uiWizardRuntimeStorage->getManager(),
        );
    }

    protected function _prepareLayout()
    {
        $this->addChild('form', \M2E\Otto\Block\Adminhtml\Listing\Wizard\Category\ModeSame\Form::class);

        return parent::_prepareLayout();
    }
}
