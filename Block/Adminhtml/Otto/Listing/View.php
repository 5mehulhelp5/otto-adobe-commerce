<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing;

use M2E\Otto\Block\Adminhtml\Log\AbstractGrid;
use M2E\Otto\Block\Adminhtml\Otto\Listing\View\Switcher;

class View extends \M2E\Otto\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    private \M2E\Otto\Helper\Data $dataHelper;
    private \M2E\Otto\Helper\Url $urlHelper;
    private string $viewMode;
    private \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Otto\Helper\Data $dataHelper,
        \M2E\Otto\Helper\Url $urlHelper,
        array $data = []
    ) {
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->dataHelper = $dataHelper;
        $this->urlHelper = $urlHelper;

        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        /** @var Switcher $viewModeSwitcher */
        $viewModeSwitcher = $this->getLayout()->createBlock(Switcher::class);

        // Initialization block
        // ---------------------------------------
        $this->setId('ottoListingView');
        $this->_controller = 'adminhtml_otto_listing_view_' . $viewModeSwitcher->getSelectedParam();
        $this->viewMode = $viewModeSwitcher->getSelectedParam();
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('add');
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $listingId = $this->uiListingRuntimeStorage->getListing()->getId();

        $this->css->addFile('listing/autoAction.css');
        $this->css->addFile('listing/view.css');

        $this->jsPhp->addConstants(
            [
                '\M2E\Otto\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_PRODUCT_ID_FIELD' => AbstractGrid::LISTING_PRODUCT_ID_FIELD
            ]
        );

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->appendHelpBlock(
                [
                    'content' => __(
                        '<p>Otto Listing is a group of Magento Products sold on a certain Shop
                    from a particular Account. M2E Otto has several options to display the content of
                    Listings referring to different data details. Each of the view options contains a
                    unique set of available Actions accessible in the Mass Actions drop-down.</p>'
                    ),
                ]
            );

            $this->setPageActionsBlock(
                \M2E\Otto\Block\Adminhtml\Otto\Listing\View\Switcher::class,
                'otto_listing_view_switcher'
            );
        }

        // ---------------------------------------
        $backUrl = $this->urlHelper->getBackUrl('*/otto_listing/index');

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $backUrl . '\');',
                'class' => 'back',
            ]
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/otto_log_listing_product',
            [
                \M2E\Otto\Block\Adminhtml\Log\AbstractGrid::LISTING_ID_FIELD =>
                    $listingId,
            ]
        );
        $this->addButton(
            'view_log',
            [
                'label' => __('Logs & Events'),
                'onclick' => 'window.open(\'' . $url . '\',\'_blank\')',
            ]
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->addButton(
            'edit_templates',
            [
                'label' => __('Edit Settings'),
                'onclick' => '',
                'class' => 'drop_down edit_default_settings_drop_down primary',
                'class_name' => \M2E\Otto\Block\Adminhtml\Magento\Button\DropDown::class,
                'options' => $this->getSettingsButtonDropDownItems(),
            ]
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/listing_wizard/create',
            [
                'listing_id' => $listingId,
                'type' => \M2E\Otto\Model\Listing\Wizard::TYPE_GENERAL,
            ]
        );

        $this->addButton(
            'listing_product_wizard',
            [
                'id' => 'listing_product_wizard',
                'label' => __('Add Products'),
                'class' => 'add primary',
                'onclick' => "setLocation('$url')",
            ]
        );
        // ---------------------------------------

        $this->addGrid();

        return parent::_prepareLayout();
    }

    private function addGrid(): void
    {
        switch ($this->viewMode) {
            case Switcher::VIEW_MODE_OTTO:
                $gridClass = \M2E\Otto\Block\Adminhtml\Otto\Listing\View\Otto\Grid::class;
                break;
            case Switcher::VIEW_MODE_MAGENTO:
                $gridClass = \M2E\Otto\Block\Adminhtml\Otto\Listing\View\Magento\Grid::class;
                break;
            case Switcher::VIEW_MODE_SETTINGS:
                $gridClass = \M2E\Otto\Block\Adminhtml\Otto\Listing\View\Settings\Grid::class;
                break;
            default:
                throw new \M2E\Otto\Model\Exception\Logic(sprintf('Unknown view mode - %s', $this->viewMode));
        }

        $this->addChild(
            'grid',
            $gridClass,
            ['listing' => $this->uiListingRuntimeStorage->getListing()]
        );
    }

    protected function _toHtml(): string
    {
        return '<div id="listing_view_progress_bar"></div>' .
            '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
            '<div id="listing_view_content_container">' .
            parent::_toHtml() .
            '</div>';
    }

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        /** @var \M2E\Otto\Block\Adminhtml\Listing\View\Header $viewHeaderBlock */
        $viewHeaderBlock = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Listing\View\Header::class,
            '',
            ['listing' => $this->uiListingRuntimeStorage->getListing()]
        );
        $viewHeaderBlock->setListingViewMode(true);

        $helper = $this->dataHelper;

        $this->jsUrl->addUrls(
            $helper->getControllerActions(
                'Otto\Listing',
                ['_current' => true]
            )
        );

        $path = 'otto_listing/transferring/index';
        $this->jsUrl->add(
            $this->getUrl(
                '*/' . $path,
                [
                    'listing_id' => $this->uiListingRuntimeStorage->getListing()->getId(),
                ]
            ),
            $path
        );

        $this->jsUrl->add(
            $this->getUrl(
                '*/listing_mapping/mapProductPopupHtml',
                [
                    'account_id' => $this->uiListingRuntimeStorage->getListing()->getAccountId()
                ]
            ),
            'mapProductPopupHtml'
        );
        $this->jsUrl->add($this->getUrl('*/listing_mapping/remap'), 'listing_mapping/remap');

        $path = 'otto_listing_transferring/getListings';
        $this->jsUrl->add($this->getUrl('*/' . $path), $path);

        $this->jsTranslator->addTranslations(
            [
                'Remove Category' => __('Remove Category'),
                'Add New Rule' => __('Add New Rule'),
                'Add/Edit Categories Rule' => __('Add/Edit Categories Rule'),
                'Based on Magento Categories' => __('Based on Magento Categories'),
                'You must select at least 1 Category.' => __('You must select at least 1 Category.'),
                'Rule with the same Title already exists.' => __('Rule with the same Title already exists.'),
                'Compatibility Attribute' => __('Compatibility Attribute'),
                'Create new' => __('Create new'),
                'Linking Product' => __('Linking Product'),
            ]
        );

        return $viewHeaderBlock->toHtml() . parent::getGridHtml();
    }

    private function getSettingsButtonDropDownItems(): array
    {
        $listingId = $this->uiListingRuntimeStorage->getListing()->getId();
        $items = [];

        $backUrl = $this->urlHelper->makeBackUrlParam(
            '*/otto_listing/view',
            ['id' => $listingId]
        );

        $url = $this->getUrl(
            '*/otto_listing/edit',
            [
                'id' => $listingId,
                'back' => $backUrl,
            ]
        );
        $items[] = [
            'label' => __('Configuration'),
            'onclick' => 'window.open(\'' . $url . '\',\'_blank\');',
            'default' => true,
        ];

        return $items;
    }
}
