<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\ShippingProfile;

class DeleteCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shippingProfileId;

    public function __construct(string $accountHash, string $shippingProfileId)
    {
        $this->accountHash = $accountHash;
        $this->shippingProfileId = $shippingProfileId;
    }

    public function getCommand(): array
    {
        return ['shippingProfile', 'delete', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shipping_profile_id' => $this->shippingProfileId,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response {
        return $response;
    }
}
