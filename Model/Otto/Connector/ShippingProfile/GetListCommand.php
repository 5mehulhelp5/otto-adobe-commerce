<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Otto\Connector\ShippingProfile;

class GetListCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;

    public function __construct(string $accountHash)
    {
        $this->accountHash = $accountHash;
    }

    public function getCommand(): array
    {
        return ['shippingProfile', 'get', 'entities'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfileCollection
    {
        $collection = new \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfileCollection();

        foreach ($response->getResponseData()['shipping_profiles'] ?? [] as $shippingProfileData) {
            $collection->add(
                new \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile(
                    $shippingProfileData['id'],
                    $shippingProfileData['name'],
                    $shippingProfileData['working_days'],
                    $shippingProfileData['order_cutoff'],
                    $shippingProfileData['delivery_type'],
                    $shippingProfileData['default_processing_time'],
                    $shippingProfileData['transport_time']
                )
            );
        }

        return $collection;
    }
}
