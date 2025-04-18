<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing\Edit;

class SelectStoreView extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractMain
{
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Listing\Repository $listingRepository,
        \M2E\Otto\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params['id'])) {
            return $this->getResponse()->setBody(__('You should provide correct parameters.'));
        }

        $listing = $this->listingRepository->get((int) $params['id']);

        $this->setAjaxContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Listing\Edit\EditStoreView::class,
                '',
                ['listing' => $listing]
            )
        );

        return $this->getResult();
    }
}
