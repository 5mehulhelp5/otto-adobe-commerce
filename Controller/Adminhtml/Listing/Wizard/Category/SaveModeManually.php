<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\Otto\Controller\Adminhtml\Otto\AbstractListing;

class SaveModeManually extends AbstractListing
{
    use \M2E\Otto\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Otto\Model\Listing\Wizard\Repository $wizardRepository;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Otto\Model\Listing\Wizard\Repository $wizardRepository
    ) {
        parent::__construct();

        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->wizardRepository = $wizardRepository;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);

        $templateData = $this->getRequest()->getParam('template_data');
        $templateData = (array)\M2E\Core\Helper\Json::decode($templateData);

        foreach ($this->getRequestIds('products_id') as $productsId) {
            $wizardProduct = $manager->findProductById((int)$productsId);

            if ($wizardProduct === null) {
                continue;
            }

            $wizardProduct->setCategoryId($templateData['categoryId']);
            $this->wizardRepository->saveProduct($wizardProduct);
        }

        return $this->getResult();
    }
}
