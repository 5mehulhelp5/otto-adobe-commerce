<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\Template\Switcher;

class Initialization extends \M2E\Otto\Block\Adminhtml\Magento\AbstractBlock
{
    private \M2E\Otto\Helper\Data $dataHelper;
    private \M2E\Otto\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\Otto\Helper\Data $dataHelper,
        \M2E\Otto\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->globalDataHelper = $globalDataHelper;
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('OttoListingTemplateSwitcherInitialization');
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        // ---------------------------------------
        $urls = [];

        // initiate account param
        // ---------------------------------------
        $account = $this->globalDataHelper->getValue('otto_account');
        $params['account_id'] = $account->getId();
        // ---------------------------------------

        // initiate attribute sets param
        // ---------------------------------------
        if (
            $this->getMode(
            ) == \M2E\Otto\Block\Adminhtml\Otto\Listing\Template\Switcher::MODE_LISTING_PRODUCT
        ) {
            $attributeSets = $this->globalDataHelper->getValue('otto_attribute_sets');
            $params['attribute_sets'] = implode(',', $attributeSets);
        }
        // ---------------------------------------

        // initiate display use default option param
        // ---------------------------------------
        $displayUseDefaultOption = $this->globalDataHelper->getValue('otto_display_use_default_option');
        $params['display_use_default_option'] = (int)(bool)$displayUseDefaultOption;
        // ---------------------------------------

        $path = 'otto_template/getTemplateHtml';
        $urls[$path] = $this->getUrl('*/' . $path, $params);
        //------------------------------

        //------------------------------
        $path = 'otto_template/isTitleUnique';
        $urls[$path] = $this->getUrl('*/' . $path);

        $path = 'otto_template/newTemplateHtml';
        $urls[$path] = $this->getUrl('*/' . $path);

        $path = 'otto_template/edit';
        $urls[$path] = $this->getUrl(
            '*/otto_template/edit',
            ['wizard' => (bool)$this->getRequest()->getParam('wizard', false)]
        );
        //------------------------------

        $this->jsUrl->addUrls($urls);
        $this->jsUrl->add(
            $this->getUrl('*/template/checkMessages'),
            'templateCheckMessages'
        );

        $this->jsPhp->addConstants(
            \M2E\Otto\Helper\Data::getClassConstants(\M2E\Otto\Model\Otto\Template\Manager::class)
        );

        $this->jsTranslator->addTranslations([
            'Customized' => __('Customized'),
            'Policies' => __('Policies'),
            'Policy with the same Title already exists.' => __('Policy with the same Title already exists.'),
            'Please specify Policy Title' => __('Please specify Policy Title'),
            'Save New Policy' => __('Save New Policy'),
            'Save as New Policy' => __('Save as New Policy'),
        ]);

        $store = $this->globalDataHelper->getValue('otto_store');

        $this->js->add(
            <<<JS
    define('Switcher/Initialization',[
        'Otto/Otto/Listing/Template/Switcher',
        'Otto/TemplateManager'
    ], function(){
        window.TemplateManagerObj = new TemplateManager();

        window.OttoListingTemplateSwitcherObj = new OttoListingTemplateSwitcher();
        OttoListingTemplateSwitcherObj.storeId = {$store->getId()};
        OttoListingTemplateSwitcherObj.listingProductIds = '{$this->getRequest()->getParam('ids')}';

    });
JS
        );

        return parent::_toHtml();
    }
}
