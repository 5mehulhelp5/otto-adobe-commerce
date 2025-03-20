<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Other\Updater;

class ServerToOttoProductConverter
{
    private \M2E\Otto\Model\Account $account;

    public function __construct(\M2E\Otto\Model\Account $account)
    {
        $this->account = $account;
    }

    public function convert(array $response): \M2E\Otto\Model\Listing\Other\OttoProductCollection
    {
        $result = new \M2E\Otto\Model\Listing\Other\OttoProductCollection();
        foreach ($response as $unmanagedItem) {
            $status = !empty($unmanagedItem['is_active']) ? \M2E\Otto\Model\Product::STATUS_LISTED : \M2E\Otto\Model\Product::STATUS_INACTIVE;

            $ottoProduct = new \M2E\Otto\Model\Listing\Other\OttoProduct(
                $this->account->getId(),
                $status,
                $unmanagedItem['product_reference'],
                $unmanagedItem['sku'],
                $unmanagedItem['ean'],
                $unmanagedItem['moin'] ?? null,
                $unmanagedItem['title'] ?? '--',
                $unmanagedItem['currency_code'],
                (float)$unmanagedItem['price'],
                $unmanagedItem['qty'] ?? 0,
                $unmanagedItem['vat'],
                $unmanagedItem['brand_id'],
                $unmanagedItem['category'],
                $unmanagedItem['media_assets'],
                $unmanagedItem['delivery'] ?? [],
                $unmanagedItem['product_url'] ?? null,
                $unmanagedItem['qty_actualize_date'] ?? null,
                $unmanagedItem['price_actualize_date'],
                $unmanagedItem['marketplace_status'],
                $unmanagedItem['shipping_profile_id'],
                $this->createMarketplaceErrorsCollection($unmanagedItem['marketplace_errors'] ?? [])
            );

            $result->add($ottoProduct);
        }

        return $result;
    }

    private function createMarketplaceErrorsCollection(array $marketplaceErrors): ?\M2E\Core\Model\Connector\Response\MessageCollection
    {
        if (empty($marketplaceErrors)) {
            return null;
        }

        $messages = [];
        foreach ($marketplaceErrors as $marketplaceError) {
            $message = new \M2E\Core\Model\Connector\Response\Message();
            $message->initFromPreparedData(
                $marketplaceError['text'],
                $marketplaceError['type'],
                \M2E\Core\Model\Connector\Response\Message::SENDER_COMPONENT,
                $marketplaceError['code']
            );
            $messages[] = $message;
        }

        return new \M2E\Core\Model\Connector\Response\MessageCollection($messages);
    }
}
