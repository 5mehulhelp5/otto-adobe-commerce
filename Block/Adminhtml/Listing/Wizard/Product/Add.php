<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Listing\Wizard\Product;

class Add extends \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    use \M2E\Otto\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private string $sourceMode;
    private \M2E\Otto\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        string $sourceMode,
        \M2E\Otto\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->sourceMode = $sourceMode;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;
        parent::__construct($context, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------

        $this->setId('listingProduct');
        $this->_controller = 'adminhtml_otto_listing_wizard_add';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = __('Select Products');
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('add');
        // ---------------------------------------

        $this->css->addFile('listing/autoAction.css');

        // ---------------------------------------

        $this->prepareButtons(
            [
                'label' => __('Continue'),
                'class' => 'action-primary forward',
                'onclick' => 'ListingProductAdd.continue();',
            ],
            $this->uiWizardRuntimeStorage->getManager(),
        );

        $this->jsTranslator->addTranslations([
            'Remove Category' => __('Remove Category'),
            'Add New Rule' => __('Add New Rule'),
            'Add/Edit Categories Rule' => __('Add/Edit Categories Rule'),
            'Start Configure' => __('Start Configure'),
        ]);

        $this->addGrid();

        return parent::_prepareLayout();
    }

    private function addGrid(): void
    {
        switch ($this->sourceMode) {
            case \M2E\Otto\Block\Adminhtml\Listing\Wizard\ProductSourceSelect::MODE_PRODUCT:
                $gridClass = \M2E\Otto\Block\Adminhtml\Listing\Wizard\Product\Add\Grid::class;
                break;

            case \M2E\Otto\Block\Adminhtml\Listing\Wizard\ProductSourceSelect::MODE_CATEGORY:
                $gridClass = \M2E\Otto\Block\Adminhtml\Listing\Wizard\Product\Add\Category\Grid::class;
                break;

            default:
                throw new \M2E\Otto\Model\Exception\Logic(sprintf('Unknown source mode - %s', $this->sourceMode));
        }

        $this->addChild(
            'grid',
            $gridClass,
        );
    }

    protected function _toHtml()
    {
        return '<div id="add_products_progress_bar"></div>' .
            '<div id="add_products_container">' .
            parent::_toHtml() .
            '</div>'
            . $this->getAutoactionPopupHtml();
    }

    private function getAutoactionPopupHtml()
    {
        return <<<HTML
<div id="autoaction_popup_content" style="display: none">
    <div style="margin-top: 10px;">
        {$this->__(
            '<h3>
 Do you want to set up a Rule by which Products will be automatically Added or
 Deleted from the current M2E Otto Listing?
</h3>
Click <b>Start Configure</b> to create a Rule or <b>Cancel</b> if you do not want to do it now.
<br/><br/>
<b>Note:</b> You can always return to it by clicking Auto Add/Remove Rules Button on this Page.',
        )}
    </div>
</div>
HTML;
    }
}
