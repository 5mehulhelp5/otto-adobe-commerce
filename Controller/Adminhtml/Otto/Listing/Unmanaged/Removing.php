<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Otto\Listing\Unmanaged;

class Removing extends \M2E\Otto\Controller\Adminhtml\Otto\AbstractListing
{
    private \M2E\Otto\Model\Listing\Other\Repository $listingOtherRepository;

    public function __construct(
        \M2E\Otto\Model\Listing\Other\Repository $listingOtherRepository
    ) {
        parent::__construct();
        $this->listingOtherRepository = $listingOtherRepository;
    }

    public function execute()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (!$productIds) {
            $this->setAjaxContent('0', false);

            return $this->getResult();
        }

        $productArray = explode(',', $productIds);

        if (empty($productArray)) {
            $this->setAjaxContent('0', false);

            return $this->getResult();
        }

        foreach ($productArray as $productId) {
            $listingOther = $this->listingOtherRepository->get((int)$productId);

            if ($listingOther->hasMagentoProductId()) {
                $listingOther->unmapProduct();
            }

            $listingOther->delete();
        }

        $this->setAjaxContent('1', false);

        return $this->getResult();
    }
}
