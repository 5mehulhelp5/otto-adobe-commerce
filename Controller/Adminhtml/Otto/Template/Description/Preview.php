<?php

namespace M2E\Otto\Controller\Adminhtml\Otto\Template\Description;

use M2E\Otto\Controller\Adminhtml\Otto\Template\AbstractDescription;
use M2E\Otto\Model\Template\Description as DescriptionAlias;

class Preview extends AbstractDescription
{
    private \M2E\Otto\Model\Otto\Listing\Product\Description\RendererFactory $rendererFactory;
    private \M2E\Otto\Model\Otto\Listing\Product\Description\TemplateParser $templateParser;
    private array $description = [];
    private \M2E\Otto\Model\ResourceModel\Listing $listingResource;
    private \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Otto\Model\Magento\ProductFactory $ourMagentoProductFactory;

    public function __construct(
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\ResourceModel\Listing $listingResource,
        \M2E\Otto\Model\Otto\Listing\Product\Description\RendererFactory $rendererFactory,
        \M2E\Otto\Model\Otto\Listing\Product\Description\TemplateParser $templateParser,
        \Magento\Framework\HTTP\PhpEnvironment\Request $phpEnvironmentRequest,
        \Magento\Catalog\Model\Product $productModel,
        \M2E\Otto\Model\Otto\Template\Manager $templateManager,
        \M2E\Otto\Model\Magento\ProductFactory $ourMagentoProductFactory
    ) {
        parent::__construct($phpEnvironmentRequest, $productModel, $templateManager);

        $this->rendererFactory = $rendererFactory;
        $this->templateParser = $templateParser;
        $this->listingResource = $listingResource;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->ourMagentoProductFactory = $ourMagentoProductFactory;
    }

    protected function getLayoutType()
    {
        return self::LAYOUT_BLANK;
    }

    public function execute()
    {
        $this->description = $this->getRequest()->getPost('description_preview', []);

        if (empty($this->description)) {
            $this->messageManager->addError((string)__('Description Policy data is not specified.'));

            return $this->getResult();
        }

        $productsEntities = $this->getProductsEntities();

        if ($productsEntities['magento_product'] === null) {
            $this->messageManager->addError((string)__('Magento Product does not exist.'));

            return $this->getResult();
        }

        $description = $this->getDescription(
            $productsEntities['magento_product'],
            $productsEntities['listing_product'],
        );

        if (!$description) {
            $this->messageManager->addWarning(
                (string)__(
                    'The Product Description attribute is selected as a source of the %channel_title Item Description,
                    but this Product has empty description.',
                    ['channel_title' => \M2E\Otto\Helper\Module::getChannelTitle()]
                ),
            );
        } elseif ($productsEntities['listing_product'] === null) {
            $this->messageManager->addWarning(
                (string)__(
                    'The Product you selected is not presented in any %extension_title Listing.
                    Thus, the values of the %extension_title Attribute(s), which are used in the Item Description,
                    will be ignored and displayed like #attribute label#.
                    Please, change the Product ID to preview the data.',
                    [
                        'extension_title' => \M2E\Otto\Helper\Module::getExtensionTitle()
                    ]
                ),
            );
        }

        $previewBlock = $this->getLayout()
                             ->createBlock(
                                 \M2E\Otto\Block\Adminhtml\Otto\Template\Description\Preview::class,
                             )
                             ->setData([
                                 'title' => $productsEntities['magento_product']->getProduct()->getData('name'),
                                 'magento_product_id' => $productsEntities['magento_product']->getProductId(),
                                 'description' => $description,
                             ]);

        $this->getResultPage()->getConfig()->getTitle()->prepend((string)__('Preview Description'));
        $this->addContent($previewBlock);

        return $this->getResult();
    }

    private function getDescription(
        \M2E\Otto\Model\Magento\Product $magentoProduct,
        \M2E\Otto\Model\Product $listingProduct = null
    ): string {
        $descriptionModeProduct = DescriptionAlias::DESCRIPTION_MODE_PRODUCT;
        $descriptionModeShort = DescriptionAlias::DESCRIPTION_MODE_SHORT;
        $descriptionModeCustom = DescriptionAlias::DESCRIPTION_MODE_CUSTOM;

        if ($this->description['description_mode'] == $descriptionModeProduct) {
            $description = $magentoProduct->getProduct()->getDescription();
        } elseif ($this->description['description_mode'] == $descriptionModeShort) {
            $description = $magentoProduct->getProduct()->getShortDescription();
        } elseif ($this->description['description_mode'] == $descriptionModeCustom) {
            $description = $this->description['description_template'];
        } else {
            $description = '';
        }

        if (empty($description)) {
            return '';
        }

        $description = $this->templateParser->parseTemplate($description, $magentoProduct);

        if ($listingProduct !== null) {
            $renderer = $this->rendererFactory->create($listingProduct);
            $description = $renderer->parseTemplate($description);
        }

        return $description;
    }

    private function getProductsEntities(): array
    {
        $productId = $this->description['magento_product_id'] ?? -1;
        $storeId = $this->description['store_id'] ?? \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        $magentoProduct = $this->getMagentoProductById($productId, $storeId);
        $listingProduct = $this->getListingProductByMagentoProductId($productId, $storeId);

        return [
            'magento_product' => $magentoProduct,
            'listing_product' => $listingProduct,
        ];
    }

    private function getMagentoProductById($productId, $storeId): ?\M2E\Otto\Model\Magento\Product
    {
        if (!$this->isMagentoProductExists($productId)) {
            return null;
        }

        $magentoProduct = $this->ourMagentoProductFactory->create();

        $magentoProduct->loadProduct($productId, $storeId);

        return $magentoProduct;
    }

    private function getListingProductByMagentoProductId($productId, $storeId): ?\M2E\Otto\Model\Product
    {
        $listingProductCollection = $this->listingProductCollectionFactory
            ->create()
            ->addFieldToFilter(\M2E\Otto\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID, $productId);

        $listingProductCollection->getSelect()->joinLeft(
            ['ml' => $this->listingResource->getMainTable()],
            '`ml`.`id` = `main_table`.`listing_id`',
            ['store_id'],
        );

        $listingProductCollection->addFieldToFilter('store_id', $storeId);
        /** @var \M2E\Otto\Model\Product $listingProduct */
        $listingProduct = $listingProductCollection->getFirstItem();

        if ($listingProduct->getId() === null) {
            return null;
        }

        return $listingProduct;
    }
}
