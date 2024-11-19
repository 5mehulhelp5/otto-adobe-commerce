<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\Create;

use M2E\Otto\Model\Listing;

class Templates extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\Otto\Helper\Data\Session $sessionDataHelper;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Otto\Helper\Data\Session $sessionDataHelper,
        array $data = []
    ) {
        $this->sessionDataHelper = $sessionDataHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoListingCreateTemplates');
        $this->_controller = 'adminhtml_otto_listing_create';
        $this->_mode = 'templates';

        $this->_headerText = __('Creating A New Listing');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/otto_listing_create/index',
            ['_current' => true, 'step' => 1]
        );
        $this->addButton(
            'back',
            [
                'label' => __('Previous Step'),
                'onclick' => 'CommonObj.backClick(\'' . $url . '\')',
                'class' => 'back',
            ]
        );

        $nextStepBtnText = __('Next Step');

        $sessionData = $this->sessionDataHelper->getValue(
            Listing::CREATE_LISTING_SESSION_DATA
        );
        if (
            isset($sessionData['creation_mode']) && $sessionData['creation_mode'] ===
            \M2E\Otto\Helper\View::LISTING_CREATION_MODE_LISTING_ONLY
        ) {
            $nextStepBtnText = __('Complete');
        }

        $url = $this->getUrl(
            '*/otto_listing_create/index',
            ['_current' => true]
        );

        $this->addButton(
            'save',
            [
                'label' => $nextStepBtnText,
                'onclick' => 'CommonObj.saveClick(\'' . $url . '\')',
                'class' => 'action-primary forward',
            ]
        );
    }

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()
                           ->createBlock(\M2E\Otto\Block\Adminhtml\Otto\Listing\Create\Breadcrumb::class);
        $breadcrumb->setSelectedStep(2);

        $helpBlock = $this->getLayout()->createBlock(\M2E\Otto\Block\Adminhtml\HelpBlock::class);
        $helpBlock->addData(
            [
                'content' => __(
                    '<p>In this section, you can optimize your listings by choosing the appropriate format, setting competitive prices
 for your items, and crafting detailed descriptions to attract more buyers.
To do this, navigate to the <b>Selling</b> and <b>Description</b> policies for your listings.</p>
 <p>You can also customize how your items synchronize with Magento Catalog data
 by defining rules in the <b>Synchronization</b> policy.</p>
<p>For more detailed instructions, refer to <a href="%url" target="_blank">our documentation</a>.</p>',
                    ['url' => 'https://docs-m2.m2epro.com/m2e-otto-policies'],
                ),
                'style' => 'margin-top: 30px',
            ]
        );

        return
            $breadcrumb->_toHtml() .
            '<div id="progress_bar"></div>' .
            $helpBlock->toHtml() .
            '<div id="content_container">' . parent::_toHtml() . '</div>';
    }
}
