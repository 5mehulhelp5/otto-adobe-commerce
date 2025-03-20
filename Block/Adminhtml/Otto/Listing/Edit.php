<?php

namespace M2E\Otto\Block\Adminhtml\Otto\Listing;

class Edit extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private ?\M2E\Otto\Model\Listing $listing = null;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Core\Helper\Url $urlHelper;

    public function __construct(
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->listingRepository = $listingRepository;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ottoListingEdit');
        $this->_controller = 'adminhtml_otto_listing';
        $this->_mode = 'create_templates';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if ($this->getRequest()->getParam('back')) {
            $url = $this->urlHelper->getBackUrl();
            $this->addButton(
                'back',
                [
                    'label' => __('Back'),
                    'onclick' => 'OttoListingSettingsObj.backClick(\'' . $url . '\')',
                    'class' => 'back',
                ]
            );
        }

        $backUrl = $this->urlHelper->getBackUrlParam('list');

        $url = $this->getUrl(
            '*/otto_listing/save',
            [
                'id' => $this->getListing()->getId(),
                'back' => $backUrl,
            ]
        );
        $saveButtonsProps = [
            'save' => [
                'label' => __('Save And Back'),
                'onclick' => 'OttoListingSettingsObj.saveClick(\'' . $url . '\')',
                'class' => 'save primary',
            ],
        ];

        $editBackUrl = $this->urlHelper->makeBackUrlParam(
            $this->getUrl(
                '*/otto_listing/edit',
                [
                    'id' => $this->listing['id'],
                    'back' => $backUrl,
                ]
            )
        );
        $url = $this->getUrl(
            '*/otto_listing/save',
            [
                'id' => $this->listing['id'],
                'back' => $editBackUrl,
            ]
        );
        $saveButtons = [
            'id' => 'save_and_continue',
            'label' => __('Save And Continue Edit'),
            'class' => 'add',
            'button_class' => '',
            'onclick' => 'OttoListingSettingsObj.saveAndEditClick(\'' . $url . '\', 1)',
            'class_name' => \M2E\Otto\Block\Adminhtml\Magento\Button\SplitButton::class,
            'options' => $saveButtonsProps,
        ];

        $this->addButton('save_buttons', $saveButtons);
    }

    public function getFormHtml()
    {
        $viewHeaderBlock = $this->getLayout()->createBlock(
            \M2E\Otto\Block\Adminhtml\Listing\View\Header::class,
            '',
            ['listing' => $this->getListing()]
        );

        return $viewHeaderBlock->toHtml() . parent::getFormHtml();
    }

    protected function getListing(): ?\M2E\Otto\Model\Listing
    {
        if ($this->listing === null && $this->getRequest()->getParam('id')) {
            $this->listing = $this->listingRepository->get($this->getRequest()->getParam('id'));
        }

        return $this->listing;
    }
}
