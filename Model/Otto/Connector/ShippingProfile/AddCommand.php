<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\ShippingProfile;

use M2E\Otto\Model\Otto\Connector\Account\Add;

class AddCommand implements \M2E\Otto\Model\Connector\CommandInterface
{
    private string $accountHash;
    private \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile $shippingProfile;

    public function __construct(
        string $accountHash,
        \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile $shippingProfile
    ) {
        $this->accountHash = $accountHash;
        $this->shippingProfile = $shippingProfile;
    }

    public function getCommand(): array
    {
        return ['shippingProfile', 'create', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shipping_profile' => [
                'name' => $this->shippingProfile->getShippingProfileName(),
                'working_days' => $this->shippingProfile->getWorkingDays(),
                'order_cutoff' => $this->shippingProfile->getOrderCutoff(),
                'delivery_type' => $this->shippingProfile->getDeliveryType(),
                'default_processing_time' => $this->shippingProfile->getDefaultProcessingTime(),
                'transport_time' => $this->shippingProfile->getTransportTime(),
            ],
        ];
    }

    public function parseResponse(\M2E\Otto\Model\Connector\Response $response): \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile
    {
        $responseData = $response->getResponseData();

        if ($response->getMessageCollection()->hasErrors()) {
            throw new \M2E\Otto\Model\Exception\ShippingProfilesUnableProcess($response->getMessageCollection()->getErrors());
        }

        return new \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile(
            $responseData['shipping_profile_id'],
            $this->shippingProfile->getShippingProfileName(),
            $this->shippingProfile->getWorkingDays(),
            $this->shippingProfile->getOrderCutoff(),
            $this->shippingProfile->getDeliveryType(),
            $this->shippingProfile->getDefaultProcessingTime(),
            $this->shippingProfile->getTransportTime()
        );
    }
}
