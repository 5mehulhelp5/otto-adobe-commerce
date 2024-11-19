<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard\Product;

use M2E\Otto\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class CompleteStep extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    use \M2E\Otto\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();

        $manager = $this->wizardManagerFactory->createById($id);

        $manager->completeStep(StepDeclarationCollectionFactory::STEP_SELECT_PRODUCTS);

        return $this->redirectToIndex($id);
    }
}
