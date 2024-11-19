<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\Order\Packages\Ship;

class PositionItem
{
    private string $positionItemId;
    private string $salesOrderId;
    private ?string $returnCarrierCode;
    private ?string $returnTrackingNumber;
    private ?string $productDeliveryType;

    public function __construct(
        string $positionItemId,
        string $salesOrderId,
        ?string $returnCarrierCode,
        ?string $returnTrackingNumber,
        ?string $productDeliveryType
    ) {
        $this->positionItemId = $positionItemId;
        $this->salesOrderId = $salesOrderId;
        $this->returnCarrierCode = $returnCarrierCode;
        $this->returnTrackingNumber = $returnTrackingNumber;
        $this->productDeliveryType = $productDeliveryType;
    }

    public function toArray(): array
    {
        $data = [
            'position_item_id' => $this->positionItemId,
            'sales_order_id' => $this->salesOrderId,
        ];

        if ($this->returnCarrierCode !== null) {
            $data['return_carrier_code'] = $this->returnCarrierCode;
        }

        if ($this->returnTrackingNumber !== null) {
            $data['return_tracking_number'] = $this->returnTrackingNumber;
        }

        if ($this->productDeliveryType !== null) {
            $data['product_delivery_type'] = $this->productDeliveryType;
        }

        return $data;
    }
}
