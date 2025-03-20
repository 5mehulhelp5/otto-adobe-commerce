<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping;

class ShippingService
{
    private const CACHE_LIFETIME_THIRTY_MINUTES = 1800;
    private const CACHE_KEY_OTTO_SHIPPING_SYNC = 'otto.shipping.sync';

    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Template\Shipping\Synchronization $syncProfiles;
    private \M2E\Otto\Helper\Data\Cache\Permanent $cache;
    private \M2E\Otto\Model\Template\Shipping\Channel\Delete $deleteOnChannel;
    private \M2E\Otto\Model\Template\Shipping\Channel\Create $createOnChannel;
    private \M2E\Otto\Model\Template\Shipping\Channel\Update $updateOnChannel;

    public function __construct(
        \M2E\Otto\Model\Template\Shipping\Channel\Update $updateOnChannel,
        \M2E\Otto\Model\Template\Shipping\Channel\Create $createOnChannel,
        \M2E\Otto\Model\Template\Shipping\Channel\Delete $deleteOnChannel,
        \M2E\Otto\Model\Template\Shipping\Synchronization $syncProfiles,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Helper\Data\Cache\Permanent $cache
    ) {
        $this->updateOnChannel = $updateOnChannel;
        $this->createOnChannel = $createOnChannel;
        $this->deleteOnChannel = $deleteOnChannel;
        $this->cache = $cache;
        $this->syncProfiles = $syncProfiles;
        $this->accountRepository = $accountRepository;
    }

    public function silenceSync(): void
    {
        try {
            $this->sync();
        } catch (\M2E\Core\Model\Exception $e) {
        }
    }

    public function sync(): void
    {
        if (!$this->isNeedSync()) {
            return;
        }

        foreach ($this->accountRepository->getAll() as $account) {
                $this->syncProfiles->process($account);
        }

        $this->markAsSynced();
    }

    public function deleteOnChannel(\M2E\Otto\Model\Template\Shipping $policy): void
    {
        $this->deleteOnChannel->process($policy);
    }

    public function createOnChannel(
        \M2E\Otto\Model\Account $account,
        \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile $channelProfile
    ): \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile {
        return $this->createOnChannel->process($account, $channelProfile);
    }

    public function updateOnChannel(
        \M2E\Otto\Model\Account $account,
        \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile $channelProfile
    ): void {
        $this->updateOnChannel->process($account, $channelProfile);
    }

    private function isNeedSync(): bool
    {
        return $this->cache->getValue(self::CACHE_KEY_OTTO_SHIPPING_SYNC) === null;
    }

    private function markAsSynced(): void
    {
        $this->cache->setValue(self::CACHE_KEY_OTTO_SHIPPING_SYNC, true, [], self::CACHE_LIFETIME_THIRTY_MINUTES);
    }

    public function createChannelShippingProfile(array $data): \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile
    {
        return new \M2E\Otto\Model\Template\Shipping\Channel\ShippingProfile(
            $data['shipping_profile_id'] ?? null,
            $data['title'],
            $data['working_days'],
            $data['order_cutoff'],
            $data['type'],
            (int)$data['handling_time'],
            (int)$data['transport_time']
        );
    }
}
