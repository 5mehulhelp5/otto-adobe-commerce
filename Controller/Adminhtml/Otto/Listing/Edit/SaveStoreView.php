<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing\Edit;

class SaveStoreView extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractMain
{
    private \M2E\Otto\Model\Listing\ChangeStoreService $changeStoreService;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository         $listingRepository,
        \M2E\Otto\Model\Listing\ChangeStoreService $changeStoreService,
        \M2E\Otto\Controller\Adminhtml\Context     $context
    ) {
        parent::__construct($context);

        $this->listingRepository = $listingRepository;
        $this->changeStoreService = $changeStoreService;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params['id'])) {
            return $this->getResponse()->setBody(__('You should provide correct parameters.'));
        }

        $listingId = (int)$params['id'];

        $listing = $this->listingRepository->get($listingId);
        $this->changeStoreService->change($listing, (int)$params['store_id']);

        return $this->getResult();
    }
}
