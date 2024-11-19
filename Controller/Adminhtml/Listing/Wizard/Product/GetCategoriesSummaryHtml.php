<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard\Product;

use M2E\Otto\Block\Adminhtml\Listing\Wizard\Category\Add\Summary\Grid;
use M2E\Otto\Block\Adminhtml\Listing\Wizard\Category\Add\Tree;

class GetCategoriesSummaryHtml extends \M2E\Otto\Controller\Adminhtml\AbstractListing
{
    use \M2E\Otto\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\Otto\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Otto\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);
        $stepData = $manager->getStepData(\M2E\Otto\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_SELECT_PRODUCTS);
        $this->uiListingRuntimeStorage->setListing($manager->getListing());

        $productsIds = $stepData['products_ids'] ?? [];

        /** @var \M2E\Otto\Block\Adminhtml\Listing\Wizard\Category\Add\Tree $treeBlock */
        $treeBlock = $this->getLayout()->createBlock(Tree::class);
        $treeBlock->setSelectedIds($productsIds);

        /** @var \M2E\Otto\Block\Adminhtml\Listing\Wizard\Category\Add\Summary\Grid $block */
        $block = $this->getLayout()->createBlock(Grid::class);
        $block->setStoreId($manager->getListing()->getStoreId());
        $block->setProductsIds($productsIds);
        $block->setProductsForEachCategory($treeBlock->getProductsCountForEachCategory());

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
