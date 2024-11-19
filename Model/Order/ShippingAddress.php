<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order;

abstract class ShippingAddress extends \Magento\Framework\DataObject
{
    protected \Magento\Directory\Model\CountryFactory $countryFactory;
    protected \M2E\Otto\Model\Order $order;
    protected ?\Magento\Directory\Model\Country $country = null;
    protected ?\Magento\Directory\Model\Region $region = null;
    protected \Magento\Directory\Helper\Data $directoryHelper;

    public function __construct(
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \M2E\Otto\Model\Order $order,
        array $data = []
    ) {
        $this->countryFactory = $countryFactory;
        $this->directoryHelper = $directoryHelper;
        $this->order = $order;
        parent::__construct($data);
    }

    abstract public function getRawData(): array;

    abstract protected function isRegionOverrideRequired(): bool;

    public function getCountry(): ?\Magento\Directory\Model\Country
    {
        if ($this->country === null) {
            $this->country = $this->countryFactory->create();

            try {
                $this->country->loadByCode($this->getData('country_code'));
            } catch (\Exception $e) {
            }
        }

        return $this->country;
    }

    /**
     * @throws \M2E\Otto\Model\Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function getRegion()
    {
        if (!$this->getCountry()->getId()) {
            return null;
        }

        if ($this->region === null) {
            $countryRegions = $this->getCountry()->getRegionCollection();
            $countryRegions->getSelect()->where('code = ? OR default_name = ?', $this->getState());
            $this->region = $countryRegions->getFirstItem();
        }

        $isRegionRequired = $this->directoryHelper->isRegionRequired($this->getCountry()->getId());
        if ($isRegionRequired && !$this->region->getId()) {
            if (!$this->isRegionOverrideRequired()) {
                throw new \M2E\Otto\Model\Exception(
                    sprintf('Invalid Region/State value "%s" in the Shipping Address.', $this->getState())
                );
            }

            $countryRegions = $this->getCountry()->getRegionCollection();
            $this->region = $countryRegions->getFirstItem();
            $msg = ' Invalid Region/State value: "%s" in the Shipping Address is overridden by "%s".';
            $this->order->addInfoLog(sprintf($msg, $this->getState(), $this->region->getDefaultName()), [], [], true);
        }

        return $this->region;
    }

    public function getCountryName()
    {
        if (!$this->getCountry()->getId()) {
            return $this->getData('country_code');
        }

        return $this->getCountry()->getName();
    }

    public function getRegionId()
    {
        $region = $this->getRegion();

        if ($region === null || $region->getId() === null) {
            return null;
        }

        return $region->getId();
    }

    public function getRegionCode()
    {
        $region = $this->getRegion();

        if ($region === null || $region->getId() === null) {
            return '';
        }

        return $region->getCode();
    }

    public function hasSameBuyerAndRecipient(): bool
    {
        $rawAddressData = $this->order->getShippingAddress()->getRawData();

        $buyerNameParts = array_map('strtolower', explode(' ', $rawAddressData['buyer_name']));
        $recipientNameParts = array_map('strtolower', explode(' ', $rawAddressData['recipient_name']));

        $buyerNameParts = array_map('trim', $buyerNameParts);
        $recipientNameParts = array_map('trim', $recipientNameParts);

        sort($buyerNameParts);
        sort($recipientNameParts);

        return count(array_diff($buyerNameParts, $recipientNameParts)) == 0;
    }

    protected function getState()
    {
        return $this->getData('state');
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        if (empty(array_filter($this->_data))) {
            return true;
        }

        return false;
    }
}
