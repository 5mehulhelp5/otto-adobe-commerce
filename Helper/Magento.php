<?php

declare(strict_types=1);

namespace M2E\Otto\Helper;

class Magento
{
    private \Magento\Directory\Model\CountryFactory $countryFactory;
    private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->countryFactory = $countryFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function getBaseShippingCountry(): string
    {
        $countryCode = (string)$this->scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $country = $this->countryFactory->create()->loadByCode($countryCode);

        return $country->getData('iso3_code');
    }

    public function getBaseShippingZip(): string
    {
        return (string)$this->scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBaseShippingCity(): string
    {
        return (string)$this->scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
