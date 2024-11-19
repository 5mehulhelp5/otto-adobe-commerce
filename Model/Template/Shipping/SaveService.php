<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template\Shipping;

use M2E\Otto\Model\Exception;
use M2E\Otto\Model\Template\Shipping;

class SaveService
{
    private \M2E\Otto\Model\Template\ShippingFactory $shippingFactory;
    private Shipping\Repository $shippingRepository;
    private Shipping\AffectedListingsProductsFactory $affectedProductsFactory;
    private Shipping\ChangeProcessorFactory $changeProcessorFactory;
    private \M2E\Otto\Model\Template\Shipping\ShippingService $shippingService;
    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Template\Shipping\ShippingDiffStub $shippingDiffStub;

    public function __construct(
        \M2E\Otto\Model\Template\Shipping\ShippingDiffStub $shippingDiffStub,
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \M2E\Otto\Model\Template\Shipping\ShippingService $shippingService,
        \M2E\Otto\Model\Template\ShippingFactory $shippingFactory,
        Shipping\ChangeProcessorFactory $changeProcessorFactory,
        Shipping\AffectedListingsProductsFactory $affectedProductsFactory,
        Shipping\Repository $shippingRepository
    ) {
        $this->shippingDiffStub = $shippingDiffStub;
        $this->accountRepository = $accountRepository;
        $this->shippingService = $shippingService;
        $this->shippingFactory = $shippingFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->affectedProductsFactory = $affectedProductsFactory;
        $this->shippingRepository = $shippingRepository;
    }

    public function save(array $data): \M2E\Otto\Model\Template\Shipping
    {
        try {
            if (empty($data['id'])) {
                $shipping = $this->create($data);
            } else {
                $shipping = $this->update($data);
            }
        } catch (\M2E\Otto\Model\Exception\Connection\SystemError $e) {
            if ($e->getMessageCollection() !== null && $e->getMessageCollection()->hasErrorWithCode(1403)) {
                throw new \M2E\Otto\Model\Exception\AccountMissingPermissions(
                    $this->accountRepository->get((int)$data['account_id'])
                );
            }
            throw $e;
        }

        return $shipping;
    }

    private function create(array $data): \M2E\Otto\Model\Template\Shipping
    {
        $account = $this->accountRepository->get((int)$data['account_id']);
        $channelProfileModel = $this->shippingService->createChannelShippingProfile($data);
        $channelProfile = $this->shippingService->createOnChannel($account, $channelProfileModel);

        $shipping = $this->shippingFactory->create(
            $account,
            $channelProfile->getShippingProfileName(),
            $channelProfile->getDefaultProcessingTime(),
            $channelProfile->getTransportTime(),
            $channelProfile->getOrderCutoff(),
            $channelProfile->getWorkingDays(),
            $channelProfile->getDeliveryType(),
            $channelProfile->getShippingProfileId()
        );
        $this->shippingRepository->create($shipping);

        return $shipping;
    }

    private function update(array $data): \M2E\Otto\Model\Template\Shipping
    {
        $channelProfileModel = $this->shippingService->createChannelShippingProfile($data);

        $shipping = $this->shippingRepository->get((int)$data['id']);
        $account = $shipping->getAccount();

        $shipping->setTitle($data['title'])
                 ->setHandlingTimeValue((int)$data['handling_time'])
                 ->setTransportTime((int)$data['transport_time'])
                 ->setOrderCutoff($data['order_cutoff'])
                 ->setWorkingDays($data['working_days'])
                 ->setType($data['type']);

        if (empty($data['shipping_profile_id'])) {
            $channelProfile = $this->shippingService->createOnChannel($account, $channelProfileModel);
            $shipping->setShippingProfileId($channelProfile->getShippingProfileId());
            $this->shippingRepository->save($shipping);

            $this->createInstruction($shipping);

            return $shipping;
        }

        $this->shippingService->updateOnChannel($account, $channelProfileModel);
        $this->shippingRepository->save($shipping);

        return $shipping;
    }

    private function createInstruction(\M2E\Otto\Model\Template\Shipping $shipping): void
    {
        $affectedListingsProducts = $this->affectedProductsFactory->create();
        $affectedListingsProducts->setModel($shipping);

        $changeProcessor = $this->changeProcessorFactory->create();

        $changeProcessor->process(
            $this->shippingDiffStub,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );
    }
}
