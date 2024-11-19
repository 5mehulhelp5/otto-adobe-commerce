<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping;

use M2E\Otto\Model\Template\Shipping\ShippingCollection as ExistCollection;
use M2E\Otto\Model\Template\Shipping\Channel\ShippingProfileCollection as ChannelCollection;

class Synchronization
{
    private \M2E\Otto\Model\Listing\Repository $listingRepository;
    private \M2E\Otto\Model\Template\Shipping\Repository $repository;
    private \M2E\Otto\Model\Template\Shipping\Channel\GetList $getList;
    private \M2E\Otto\Model\Template\Shipping\UpdateFromChannel $updateFromChannel;
    private \M2E\Otto\Model\Template\Shipping\CreateFromChannel $createFromChannel;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Model\Template\Shipping\CreateFromChannel $createFromChannel,
        \M2E\Otto\Model\Template\Shipping\UpdateFromChannel $updateFromChannel,
        \M2E\Otto\Model\Template\Shipping\Channel\GetList $getList,
        \M2E\Otto\Model\Template\Shipping\Repository $repository
    ) {
        $this->listingRepository = $listingRepository;
        $this->createFromChannel = $createFromChannel;
        $this->updateFromChannel = $updateFromChannel;
        $this->getList = $getList;
        $this->repository = $repository;
    }

    public function process(\M2E\Otto\Model\Account $account): void
    {
        $channelCollection = $this->getList->process($account);
        $existCollection = $this->repository->findByAccount($account->getId());

        $this->removeShippingProfiles($channelCollection, $existCollection);
        $this->updateShippingProfiles($channelCollection, $existCollection);
        $this->createShippingProfiles($channelCollection, $account, $existCollection);
    }

    private function removeShippingProfiles(
        ChannelCollection $channelProfiles,
        ExistCollection $existProfiles
    ): void {
        foreach ($existProfiles->getAll() as $existProfile) {
            if (
                $channelProfiles->has($existProfile->getShippingProfileId())
                || $existProfile->getShippingProfileId() === null
            ) {
                continue;
            }

            if ($this->listingRepository->isExistListingByShippingPolicy($existProfile->getId())) {
                $existProfile->markAsDeleted();
                $this->repository->save($existProfile);
            } else {
                $this->repository->delete($existProfile);
            }

            $existProfiles->remove($existProfile->getShippingProfileId());
        }
    }

    private function updateShippingProfiles(
        ChannelCollection $channelProfiles,
        ExistCollection $existProfiles
    ): void {
        foreach ($channelProfiles->getAll() as $channelProfile) {
            $shippingProfileId = $channelProfile->getShippingProfileId();

            if (!$existProfiles->has($shippingProfileId)) {
                continue;
            }

            $this->updateFromChannel->process(
                $existProfiles->get($shippingProfileId),
                $channelProfile
            );

            $channelProfiles->remove($shippingProfileId);
        }
    }

    private function createShippingProfiles(
        ChannelCollection $channelProfiles,
        \M2E\Otto\Model\Account $account,
        ExistCollection $existProfiles
    ): void {
        foreach ($channelProfiles->getAll() as $channelProfile) {
            $existingPolicy = $this->repository->findOldPoliciesByTitle($channelProfile->getShippingProfileName());
            if ($existingPolicy) {
                $existingPolicy->setTitle('old_' . $existingPolicy->getTitle());
                $this->repository->save($existingPolicy);
            }

            $shippingProfile = $this->createFromChannel->process($account, $channelProfile);
            $existProfiles->add($shippingProfile);
        }
    }
}
