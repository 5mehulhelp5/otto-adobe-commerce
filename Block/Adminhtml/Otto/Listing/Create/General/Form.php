<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Listing\Create\General;

use M2E\Otto\Block\Adminhtml\StoreSwitcher;
use M2E\Otto\Model\Listing;

class Form extends \M2E\Otto\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\Core\Helper\Magento\Store $storeHelper;
    protected Listing $listing;
    private \M2E\Otto\Helper\Data $dataHelper;
    private \M2E\Otto\Helper\Data\Session $sessionDataHelper;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Core\Helper\Magento\Store $storeHelper,
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Otto\Helper\Data $dataHelper,
        \M2E\Otto\Helper\Data\Session $sessionDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->storeHelper = $storeHelper;
        $this->dataHelper = $dataHelper;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->accountRepository = $accountRepository;
        $this->listingRepository = $listingRepository;
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => 'javascript:void(0)',
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'general_fieldset',
            [
                'legend' => __('General'),
                'collapsable' => false,
            ]
        );

        $title = $this->getTitle();
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'value' => $title,
                'required' => true,
                'class' => 'Otto-listing-title',
                'tooltip' => __(
                    'Create a descriptive and meaningful Title for your M2E Otto Listing. <br/>
                    This is used for reference within M2E Otto and will not appear on your Otto Listings.'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'otto_settings_fieldset',
            [
                'legend' => __('Otto Settings'),
                'collapsable' => false,
            ]
        );

        $accountsData = $this->getAccountData();
        if ($accountsData['select_account_is_disabled']) {
            $fieldset->addField(
                'account_id_hidden',
                'hidden',
                [
                    'name' => 'account_id',
                    'value' => $accountsData['active_account_id'],
                ]
            );
        }

        $accountSelect = $this->elementFactory->create(
            self::SELECT,
            [
                'data' => [
                    'html_id' => 'account_id',
                    'name' => 'account_id',
                    'style' => 'width: 50%;',
                    'value' => $accountsData['active_account_id'],
                    'values' => $accountsData['accounts'],
                    'required' => $accountsData['is_required'],
                    'disabled' => $accountsData['select_account_is_disabled'],
                ],
            ]
        );
        $accountSelect->setForm($form);

        $fieldset->addField(
            'account_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Account'),
                'style' => 'line-height: 32px; display: initial;',
                'required' => $accountsData['is_required'],
                'text' => <<<HTML
    <span id="account_label"></span>
    {$accountSelect->toHtml()}
HTML
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_fieldset',
            [
                'legend' => __('Magento Settings'),
                'collapsable' => false,
            ]
        );

        $storeId = $this->getSessionData('store_id') ?? $this->storeHelper->getDefaultStoreId();
        $fieldset->addField(
            'store_id',
            self::STORE_SWITCHER,
            [
                'name' => 'store_id',
                'label' => __('Magento Store View'),
                'value' => $storeId,
                'required' => true,
                'has_empty_option' => true,
                'tooltip' => __(
                    'Choose the Magento Store View you want to use for this M2E Otto Listing.
                     Please remember that Attribute values from the selected Store View will be used in the Listing.'
                ),
                'display_default_store_mode' => StoreSwitcher::DISPLAY_DEFAULT_STORE_MODE_DOWN,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getTitle(): string
    {
        if ($fromSession = $this->getSessionData('title')) {
            return $fromSession;
        }

        return $this->listingRepository->getListingsCount() === 0
            ? (string)__('Default')
            : '';
    }

    /**
     * @return array{
     *     account_is_disabled: bool,
     *     is_required: bool,
     *     active_account_id: int,
     *     accounts: array
     * }
     */
    private function getAccountData(): array
    {
        $accounts = $this->accountRepository->getAll();

        if ($accounts === []) {
            return [
                'select_account_is_disabled' => false,
                'is_required' => 0,
                'active_account_id' => 0,
                'accounts' => [],
            ];
        }

        $data = [
            'select_account_is_disabled' => false,
            'is_required' => count($accounts) > 1,
            'active_account_id' => reset($accounts)->getId(),
            'accounts' => array_map(
                static function (\M2E\Otto\Model\Account $account) {
                    return [
                        'value' => $account->getId(),
                        'label' => $account->getTitle(),
                    ];
                },
                $accounts
            ),
        ];

        if ($sessionAccountId = $this->getSessionData('account_id')) {
            $data['active_account_id'] = $sessionAccountId;

            return $data;
        }

        if ($requestAccountId = $this->getRequest()->getParam('account_id')) {
            $data['select_account_is_disabled'] = true;
            $data['active_account_id'] = (int)$requestAccountId;
        }

        return $data;
    }

    protected function _prepareLayout()
    {
        $this->jsPhp->addConstants(
            \M2E\Otto\Helper\Data::getClassConstants(\M2E\Otto\Helper\Component\Otto::class)
        );

        $this->jsUrl->addUrls($this->dataHelper->getControllerActions('Otto\Account'));

        $this->jsUrl->addUrls(
            $this->dataHelper->getControllerActions('Otto_Listing_Create', ['_current' => true])
        );

        $this->jsUrl->add(
            $this->getUrl(
                '*/otto_account/beforeGetToken',
                [
                    'close_on_save' => true,
                    'wizard' => (bool)$this->getRequest()->getParam('wizard', false),
                ]
            ),
            'otto_account/newAction'
        );

        $this->jsUrl->add(
            $this->getUrl(
                '*/otto_synchronization_log/index',
                [
                    'wizard' => (bool)$this->getRequest()->getParam('wizard', false),
                ]
            ),
            'logViewUrl'
        );

        $this->jsTranslator->addTranslations(
            [
                'The specified Title is already used for other Listing. Listing Title must be unique.'
                => __(
                    'The specified Title is already used for other Listing. Listing Title must be unique.'
                ),
                'Account not found, please create it.'
                => __('Account not found, please create it.'),
                'Please wait while Synchronization is finished.'
                => __('Please wait while Synchronization is finished.'),
            ]
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'Otto/Otto/Listing/Create/General'
    ], function(){
        Otto.formData.wizard = {$this->getRequest()->getParam('wizard', 0)};

        window.OttoListingCreateGeneralObj = new OttoListingCreateGeneral();
    });
JS
        );

        return parent::_prepareLayout();
    }

    private function getSessionData(string $key)
    {
        $sessionData = $this->sessionDataHelper->getValue(Listing::CREATE_LISTING_SESSION_DATA);

        return $sessionData[$key] ?? null;
    }
}
