<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Order\Shipment;

class TrackingPair
{
    /** @var \M2E\Otto\Model\Order\Shipment\TrackingDetails|null */
    private ?\M2E\Otto\Model\Order\Shipment\TrackingDetails $primaryDetails;
    /** @var \M2E\Otto\Model\Order\Shipment\TrackingDetails|null */
    private ?\M2E\Otto\Model\Order\Shipment\TrackingDetails $returnDetails;

    public function __construct(
        ?\M2E\Otto\Model\Order\Shipment\TrackingDetails $primaryDetails,
        ?\M2E\Otto\Model\Order\Shipment\TrackingDetails $returnDetails
    ) {
        $this->primaryDetails = $primaryDetails;
        $this->returnDetails = $returnDetails;
    }

    public function hasPrimaryDetails(): bool
    {
        return $this->primaryDetails !== null;
    }

    /** @psalm-suppress InvalidNullableReturnType */
    public function getPrimaryDetails(): \M2E\Otto\Model\Order\Shipment\TrackingDetails
    {
        /** @psalm-suppress NullableReturnStatement */
        return $this->primaryDetails;
    }

    public function hasReturnDetails(): bool
    {
        return $this->returnDetails !== null;
    }

    /** @psalm-suppress InvalidNullableReturnType */
    public function getReturnDetails(): \M2E\Otto\Model\Order\Shipment\TrackingDetails
    {
        /** @psalm-suppress NullableReturnStatement */
        return $this->returnDetails;
    }
}
