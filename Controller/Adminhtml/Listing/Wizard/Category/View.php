<?php

declare(strict_types=1);

namespace M2E\Otto\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\Otto\Block\Adminhtml\Listing\Wizard\CategorySelectMode;
use M2E\Otto\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\Otto\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\Otto\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY;
    }

    protected function process(\M2E\Otto\Model\Listing $listing)
    {
        $manager = $this->getWizardManager();
        $selectedMode = $manager->getStepData(StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY_MODE);

        $mode = $selectedMode['mode'];

        if ($mode === CategorySelectMode::MODE_SAME) {
            return $this->stepSelectCategoryModeSame();
        }

        if ($mode === CategorySelectMode::MODE_MANUALLY) {
            return $this->stepSelectCategoryModeManually();
        }

        throw new \LogicException('Category mode unknown.');
    }

    private function stepSelectCategoryModeSame()
    {
        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Otto\Block\Adminhtml\Listing\Wizard\Category\Same::class,
            ),
        );

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Set Category (All Products same Category)'));

        return $this->getResult();
    }

    private function stepSelectCategoryModeManually()
    {
        $manager = $this->getWizardManager();

        $wizardProducts = $manager->getProducts();

        $categoriesData = $this->getCategoriesData($wizardProducts);

        $block = $this
            ->getLayout()
            ->createBlock(
                \M2E\Otto\Block\Adminhtml\Listing\Wizard\Category\Manually::class,
                '',
                [
                    'categoriesData' => $categoriesData,
                ],
            );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->setAjaxContent($block->getChildBlock('grid')->toHtml());

            return $this->getResult();
        }

        $this->addContent($block);

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(
                 __('Set Category (Manually for each Product)'),
             );

        return $this->getResult();
    }

    /**
     * @param \M2E\Otto\Model\Listing\Wizard\Product[] $wizardProduct
     *
     * @return array
     */
    private function getCategoriesData(array $wizardProduct): array
    {
        $result = [];
        foreach ($wizardProduct as $product) {
            $productData = [
                'product_id' => $product->getId(),
            ];

            $category = $product->getCategory();
            if ($category !== null) {
                $productData['value'] = $category->getId();
                $productData['path'] = $category->getTitle();
            }

            $result[$product->getMagentoProductId()] = $productData;
        }

        return $result;
    }
}
