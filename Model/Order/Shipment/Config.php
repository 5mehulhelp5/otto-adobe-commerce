<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Shipment;

class Config
{
    private \M2E\Otto\Helper\Magento $magentoHelper;

    public function __construct(\M2E\Otto\Helper\Magento $magentoHelper)
    {
        $this->magentoHelper = $magentoHelper;
    }

    public function getBaseShippingCity(): string
    {
        return $this->magentoHelper->getBaseShippingCity();
    }

    public function getBaseShippingCountry(): string
    {
        return $this->magentoHelper->getBaseShippingCountry();
    }

    public function getBaseShippingZip(): string
    {
        return $this->magentoHelper->getBaseShippingZip();
    }
}
