<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Packages\Ship;

class Package
{
    private string $id;
    private string $carrierCode;
    private string $trackingNumber;
    private string $shipDate;
    private string $shipFromCity;
    private string $shipFromCountryCode;
    private string $shipFromZipCode;
    /** @var \M2E\Otto\Model\Otto\Connector\Order\Packages\Ship\PositionItem[] */
    private array $items;

    public function __construct(
        string $id,
        string $carrierCode,
        string $trackingNumber,
        string $shipDate,
        string $shipFromCity,
        string $shipFromCountryCode,
        string $shipFromZipCode,
        array $items
    ) {
        $this->id = $id;
        $this->carrierCode = $carrierCode;
        $this->trackingNumber = $trackingNumber;
        $this->shipDate = $shipDate;
        $this->shipFromCity = $shipFromCity;
        $this->shipFromCountryCode = $shipFromCountryCode;
        $this->shipFromZipCode = $shipFromZipCode;
        $this->items = $items;
    }

    public function toArray(): array
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }

        return [
            'id' => $this->id,
            'carrier_code' => $this->carrierCode,
            'tracking_number' => $this->trackingNumber,
            'ship_date' => $this->shipDate,
            'ship_from_city' => $this->shipFromCity,
            'ship_from_country_code' => $this->shipFromCountryCode, // must conform to the pattern "/^[A-Z]{3}$/"
            'ship_from_zip_code' => $this->shipFromZipCode,
            'items' => $items,
        ];
    }
}
