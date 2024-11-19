<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\Otto\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class AssignModeSame extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    use \M2E\Otto\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Otto\Model\Category\Repository $categoryRepository;
    private \M2E\Otto\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Otto\Model\Category\Repository $categoryRepository,
        \M2E\Otto\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->categoryRepository = $categoryRepository;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);

        $categoryData = [];
        if ($param = $this->getRequest()->getParam('category_data')) {
            $categoryData = json_decode($param, true);
        }

        $categoryId = (int)($categoryData['categoryId'] ?? 0);
        if (empty($categoryId)) {
            return $this->redirectToIndex($id);
        }

        $category = $this->categoryRepository->find($categoryId);

        $manager->setProductsCategoryIdSame($category->getId());

        $manager->completeStep(StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY);

        return $this->redirectToIndex($id);
    }
}
