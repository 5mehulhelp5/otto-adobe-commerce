<?php

declare(strict_types=1);

namespace M2E\Otto\Block\Adminhtml\Otto\Account\Edit;

use M2E\Otto\Block\Adminhtml\Otto\Account\Edit\Tabs as PageTabs;
use M2E\Otto\Model\Account\Settings\Order as OrderSettings;
use M2E\Otto\Model\Account\Settings\UnmanagedListings as UnmanagedListingsSettings;

class Tabs extends \M2E\Otto\Block\Adminhtml\Magento\Tabs\AbstractTabs
{
    public const TAB_ID_GENERAL = 'general';
    public const TAB_ID_LISTING_OTHER = 'listingOther';
    public const TAB_ID_ORDER = 'order';
    public const TAB_ID_INVOICES_AND_SHIPMENTS = 'invoices_and_shipments';

    private ?\M2E\Otto\Model\Account $account;

    public function __construct(
        \M2E\Otto\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        ?\M2E\Otto\Model\Account $account = null,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();

        $this->setId('ottoAccountEditTabs');
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        /** @var PageTabs\General $generalTabBlock */
        $generalTabBlock = $this
            ->getLayout()
            ->createBlock(PageTabs\General::class, '', [
                'account' => $this->account,
            ]);

        $this->addTab(
            self::TAB_ID_GENERAL,
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $generalTabBlock->toHtml(),
            ],
        );

        $listingOtherTabContent = $this
            ->getLayout()
            ->createBlock(PageTabs\UnmanagedListing::class, '', [
                'account' => $this->account,
            ]);
        $this->addTab(
            self::TAB_ID_LISTING_OTHER,
            [
                'label' => __('Unmanaged Listings'),
                'title' => __('Unmanaged Listings'),
                'content' => $listingOtherTabContent->toHtml(),
            ],
        );

        /** @var PageTabs\Order $orderTabBlock */
        $orderTabBlock = $this
            ->getLayout()
            ->createBlock(PageTabs\Order::class, '', [
                'account' => $this->account,
            ]);

        $this->addTab(
            self::TAB_ID_ORDER,
            [
                'label' => __('Orders'),
                'title' => __('Orders'),
                'content' => $orderTabBlock->toHtml(),
            ],
        );

        /** @var PageTabs\InvoicesAndShipments $invoicesAndShipmentsTabBlock */
        $invoicesAndShipmentsTabBlock = $this
            ->getLayout()
            ->createBlock(PageTabs\InvoicesAndShipments::class, '', [
                'account' => $this->account,
            ]);
        $this->addTab(
            self::TAB_ID_INVOICES_AND_SHIPMENTS,
            [
                'label' => __('Invoices & Shipments'),
                'title' => __('Invoices & Shipments'),
                'content' => $invoicesAndShipmentsTabBlock->toHtml(),
            ],
        );

        $this->setActiveTab($this->getRequest()->getParam('tab', self::TAB_ID_GENERAL));

        $this->jsUrl->addUrls(
            [
                'formSubmit' => $this->getUrl(
                    '*/otto_account/save',
                    ['_current' => true, 'id' => $this->getRequest()->getParam('id')],
                ),
                'deleteAction' => $this->getUrl(
                    '*/otto_account/delete',
                    ['id' => $this->getRequest()->getParam('id')],
                ),
                '*/otto_account/delete' => $this->getUrl('*/otto_account/delete'),
                'otto_account/beforeGetInstallationId' => $this->getUrl(
                    '*/otto_account/beforeGetInstallationId',
                    ['_current' => true],
                ),
                'otto_account/beforeGetToken' => $this->getUrl(
                    '*/otto_account/beforeGetToken',
                    ['_current' => true],
                ),
            ],
        );

        $this->jsPhp->addConstants(
            [
                'Account\Settings\UnmanagedListings::MAPPING_TITLE_MODE_NONE' => UnmanagedListingsSettings::MAPPING_TITLE_MODE_NONE,
                'Account\Settings\UnmanagedListings::MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE' => UnmanagedListingsSettings::MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE,
                'Account\Settings\UnmanagedListings::MAPPING_SKU_MODE_NONE' => UnmanagedListingsSettings::MAPPING_SKU_MODE_NONE,
                'Account\Settings\UnmanagedListings::MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE' => UnmanagedListingsSettings::MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE,
                'Account\Settings\UnmanagedListings::MAPPING_EAN_MODE_NONE' => UnmanagedListingsSettings::MAPPING_EAN_MODE_NONE,
                'Account\Settings\UnmanagedListings::MAPPING_EAN_MODE_CUSTOM_ATTRIBUTE' => UnmanagedListingsSettings::MAPPING_EAN_MODE_CUSTOM_ATTRIBUTE,
                'Account\Settings\Order::TAX_MODE_MIXED' => OrderSettings::TAX_MODE_MIXED,
                'Account\Settings\Order::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT' => OrderSettings::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT,
                'Account\Settings\Order::CUSTOMER_MODE_GUEST' => OrderSettings::CUSTOMER_MODE_GUEST,
                'Account\Settings\Order::NUMBER_SOURCE_MAGENTO' => OrderSettings::NUMBER_SOURCE_MAGENTO,
                'Account\Settings\Order::CUSTOMER_MODE_NEW' => OrderSettings::CUSTOMER_MODE_NEW,
                'Account\Settings\Order::CUSTOMER_MODE_PREDEFINED' => OrderSettings::CUSTOMER_MODE_PREDEFINED,
                'Account\Settings\Order::LISTINGS_STORE_MODE_DEFAULT' => OrderSettings::LISTINGS_STORE_MODE_DEFAULT,
                'Account\Settings\Order::LISTINGS_OTHER_PRODUCT_MODE_IGNORE' => OrderSettings::LISTINGS_OTHER_PRODUCT_MODE_IGNORE,
                'Account\Settings\Order::NUMBER_SOURCE_CHANNEL' => OrderSettings::NUMBER_SOURCE_CHANNEL,
                'Account\Settings\Order::LISTINGS_STORE_MODE_CUSTOM' => OrderSettings::LISTINGS_STORE_MODE_CUSTOM,
                'Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT' => OrderSettings::ORDERS_STATUS_MAPPING_MODE_DEFAULT,
                'Account\Settings\Order::ORDERS_STATUS_MAPPING_PROCESSING' => OrderSettings::ORDERS_STATUS_MAPPING_PROCESSING,
                'Account\Settings\Order::ORDERS_STATUS_MAPPING_SHIPPED' => OrderSettings::ORDERS_STATUS_MAPPING_SHIPPED,
            ],
        );

        $this->jsTranslator->add(
            'confirmation_account_delete',
            __(
                '<p>You are about to delete your %channel_title seller account from %extension_title. This will remove the
account-related Listings and Products from the extension and disconnect the synchronization.
Your listings on the channel will <b>not</b> be affected.</p>
<p>Please confirm if you would like to delete the account.</p>
<p>Note: once the account is no longer connected to your %extension_title, please remember to delete it from
<a href="%href">M2E Accounts</a></p>',
                [
                    'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    'href' => \M2E\Core\Helper\Module\Support::ACCOUNTS_URL,
                    'channel_title' => \M2E\Otto\Helper\Module::getChannelTitle(),
                ]
            ),
        );

        $this->jsTranslator->addTranslations(
            [
                'The specified Title is already used for other Account. Account Title must be unique.' => __(
                    'The specified Title is already used for other Account. Account Title must be unique.',
                ),
                sprintf(
                    'Be attentive! By Deleting Account you delete all information on it from %s Server. '
                    . 'This will cause inappropriate work of all Accounts\' copies.',
                    \M2E\Otto\Helper\Module::getExtensionTitle()
                ) => __(
                    'Be attentive! By Deleting Account you delete all information on it from %extension_title Server. '
                    . 'This will cause inappropriate work of all Accounts\' copies.',
                    [
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle(),
                    ]
                ),
                'No Customer entry is found for specified ID.' => __(
                    'No Customer entry is found for specified ID.',
                ),
                'If Yes is chosen, you must select at least one Attribute for Product Linking.' => __(
                    'If Yes is chosen, you must select at least one Attribute for Product Linking.',
                ),
                'You should create at least one Response Template.' => __(
                    'You should create at least one Response Template.',
                ),
            ],
        );

        return parent::_beforeToHtml();
    }
}
