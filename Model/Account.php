<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\ResourceModel\Account as AccountResource;

class Account extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public const LOCK_NICK = 'account';
    public const MODE_PRODUCTION = 'production';
    public const MODE_SANDBOX = 'sandbox';

    private const USE_SHIPPING_ADDRESS_AS_BILLING_ALWAYS = 0;
    private const USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT = 1;

    private \M2E\Otto\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private Account\Settings\UnmanagedListings $unmanagedListingSettings;
    private Account\Settings\Order $ordersSettings;
    private Account\Settings\InvoicesAndShipment $invoiceAndShipmentSettings;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
        );
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(AccountResource::class);
    }

    public function init(
        string $title,
        string $installationId,
        string $serverHash,
        string $mode,
        \M2E\Otto\Model\Account\Settings\UnmanagedListings $unmanagedListingsSettings,
        \M2E\Otto\Model\Account\Settings\Order $orderSettings,
        \M2E\Otto\Model\Account\Settings\InvoicesAndShipment $invoicesAndShipmentSettings
    ): self {
        $this
            ->setTitle($title)
            ->setData(AccountResource::COLUMN_INSTALLATION_ID, $installationId)
            ->setData(AccountResource::COLUMN_SERVER_HASH, $serverHash)
            ->setData(AccountResource::COLUMN_MODE, $mode)
            ->setUnmanagedListingSettings($unmanagedListingsSettings)
            ->setOrdersSettings($orderSettings)
            ->setInvoiceAndShipmentSettings($invoicesAndShipmentSettings);

        return $this;
    }

    public function getId(): int
    {
        return (int)parent::getId();
    }

    /**
     * @return \M2E\Otto\Model\Listing[]
     */
    public function getListings(): array
    {
        $listingCollection = $this->listingCollectionFactory->create();
        $listingCollection->addFieldToFilter('account_id', $this->getId());

        return $listingCollection->getItems();
    }

    // ----------------------------------------

    public function setTitle(string $title): self
    {
        $this->setData(AccountResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function getTitle()
    {
        return $this->getData(AccountResource::COLUMN_TITLE);
    }

    public function getServerHash()
    {
        return $this->getData(AccountResource::COLUMN_SERVER_HASH);
    }

    public function getInstallationId(): string
    {
        return (string)$this->getData(AccountResource::COLUMN_INSTALLATION_ID);
    }

    public function setInstallationId(string $installationId): self
    {
        $this->setData(AccountResource::COLUMN_INSTALLATION_ID, $installationId);

        return $this;
    }

    public function setUnmanagedListingSettings(
        \M2E\Otto\Model\Account\Settings\UnmanagedListings $settings
    ): self {
        $this->unmanagedListingSettings = $settings;
        $this
            ->setData(AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION, (int)$settings->isSyncEnabled())
            ->setData(AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_MODE, (int)$settings->isMappingEnabled())
            ->setData(
                AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS,
                json_encode(
                    [
                        'sku' => $settings->getMappingBySkuSettings(),
                        'ean' => $settings->getMappingByEanSettings(),
                        'title' => $settings->getMappingByTitleSettings()
                    ],
                ),
            )
            ->setData(
                AccountResource::COLUMN_OTHER_LISTINGS_RELATED_STORE_ID,
                $settings->getRelatedStoreId(),
            );

        return $this;
    }

    public function getUnmanagedListingSettings(): \M2E\Otto\Model\Account\Settings\UnmanagedListings
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->unmanagedListingSettings)) {
            return $this->unmanagedListingSettings;
        }

        $mappingSettings = $this->getData(AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_SETTINGS);
        $mappingSettings = json_decode($mappingSettings, true);

        $settings = new \M2E\Otto\Model\Account\Settings\UnmanagedListings();

        return $this->unmanagedListingSettings = $settings
            ->createWithSync((bool)$this->getData(AccountResource::COLUMN_OTHER_LISTINGS_SYNCHRONIZATION))
            ->createWithMapping((bool)$this->getData(AccountResource::COLUMN_OTHER_LISTINGS_MAPPING_MODE))
            ->createWithMappingSettings(
                $mappingSettings['sku'] ?? [],
                $mappingSettings['ean'] ?? [],
                $mappingSettings['title'] ?? [],
            )
            ->createWithRelatedStoreId(
                (int)$this->getData(AccountResource::COLUMN_OTHER_LISTINGS_RELATED_STORE_ID),
            );
    }

    public function setOrdersSettings(\M2E\Otto\Model\Account\Settings\Order $settings): self
    {
        $this->ordersSettings = $settings;

        $data = $settings->toArray();

        $this->setData(AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS, json_encode($data));

        return $this;
    }

    public function getOrdersSettings(): \M2E\Otto\Model\Account\Settings\Order
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->ordersSettings)) {
            return $this->ordersSettings;
        }

        $data = json_decode($this->getData(AccountResource::COLUMN_MAGENTO_ORDERS_SETTINGS), true);

        $settings = new \M2E\Otto\Model\Account\Settings\Order();

        return $this->ordersSettings = $settings->createWith($data);
    }

    public function setInvoiceAndShipmentSettings(
        \M2E\Otto\Model\Account\Settings\InvoicesAndShipment $settings
    ): self {
        $this->invoiceAndShipmentSettings = $settings;

        $this
            ->setData(AccountResource::COLUMN_CREATE_MAGENTO_INVOICE, (int)$settings->isCreateMagentoInvoice())
            ->setData(AccountResource::COLUMN_CREATE_MAGENTO_SHIPMENT, (int)$settings->isCreateMagentoShipment());

        return $this;
    }

    public function getInvoiceAndShipmentSettings(): \M2E\Otto\Model\Account\Settings\InvoicesAndShipment
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->invoiceAndShipmentSettings)) {
            return $this->invoiceAndShipmentSettings;
        }

        $settings = new \M2E\Otto\Model\Account\Settings\InvoicesAndShipment();

        return $this->invoiceAndShipmentSettings = $settings
            ->createWithMagentoInvoice((bool)$this->getData(AccountResource::COLUMN_CREATE_MAGENTO_INVOICE))
            ->createWithMagentoShipment((bool)$this->getData(AccountResource::COLUMN_CREATE_MAGENTO_SHIPMENT));
    }

    //region order_last_synchronization
    public function setOrdersLastSyncDate(\DateTime $date): self
    {
        $this->setData(AccountResource::COLUMN_ORDER_LAST_SYNC, $date);

        return $this;
    }

    public function getOrdersLastSyncDate(): ?\DateTime
    {
        $value = $this->getData(AccountResource::COLUMN_ORDER_LAST_SYNC);
        if (empty($value)) {
            return null;
        }

        return \M2E\Otto\Helper\Date::createDateGmt($value);
    }
    //endregion

    //region inventory_last_synchronization
    public function setInventoryLastSyncDate(\DateTime $date): self
    {
        $this->setData(AccountResource::COLUMN_INVENTORY_LAST_SYNC, $date);

        return $this;
    }

    public function getInventoryLastSyncDate(): ?\DateTime
    {
        $value = $this->getData(AccountResource::COLUMN_INVENTORY_LAST_SYNC);
        if (empty($value)) {
            return null;
        }

        return \M2E\Otto\Helper\Date::createDateGmt($value);
    }

    public function resetInventoryLastSyncData(): void
    {
        $this->setData(AccountResource::COLUMN_INVENTORY_LAST_SYNC, null);
    }
    //endregion

    public function getCreateData(): \DateTime
    {
        $value = $this->getData(AccountResource::COLUMN_CREATE_DATE);

        return \M2E\Otto\Helper\Date::createDateGmt($value);
    }

    /**
     * @return bool
     */
    public function useMagentoOrdersShippingAddressAsBillingAlways(): bool
    {
        return $this->getBillingAddressMode() == self::USE_SHIPPING_ADDRESS_AS_BILLING_ALWAYS;
    }

    /**
     * @return bool
     */
    public function useMagentoOrdersShippingAddressAsBillingIfSameCustomerAndRecipient(): bool
    {
        return $this->getBillingAddressMode() == self::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT;
    }

    /**
     * @return int
     */
    private function getBillingAddressMode(): int
    {
        return (int)$this->getSetting(
            'magento_orders_settings',
            ['customer', 'billing_address_mode'],
            self::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT
        );
    }

    public function getMode(): string
    {
        return (string)$this->getData(AccountResource::COLUMN_MODE);
    }
}
