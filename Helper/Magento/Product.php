<?php

declare(strict_types=1);

namespace M2E\Otto\Helper\Magento;

use M2E\Otto\Model\Exception;
use M2E\Otto\Model\Magento\Product as ProductModel;

class Product
{
    public const TYPE_SIMPLE = 'simple';
    public const TYPE_DOWNLOADABLE = 'downloadable';
    public const TYPE_CONFIGURABLE = 'configurable';
    public const TYPE_BUNDLE = 'bundle';
    public const TYPE_GROUPED = 'grouped';

    public const SKU_MAX_LENGTH = 64;

    /** @var array */
    private $cacheLoadedProducts = [];

    /** @var \Magento\Catalog\Model\ProductFactory */
    private $productFactory;
    /** @var \Magento\CatalogInventory\Model\Configuration */
    private $catalogInventoryConfiguration;
    /** @var \M2E\Otto\Model\Config\Manager */
    private $config;
    /** @var \M2E\Otto\Helper\Data\Cache\Runtime */
    private $runtimeCache;

    public function __construct(
        \M2E\Otto\Helper\Data\Cache\Runtime $runtimeCache,
        \M2E\Otto\Model\Config\Manager $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Model\Configuration $catalogInventoryConfiguration
    ) {
        $this->productFactory = $productFactory;
        $this->catalogInventoryConfiguration = $catalogInventoryConfiguration;
        $this->config = $config;
        $this->runtimeCache = $runtimeCache;
    }

    // ----------------------------------------

    public function isSimpleType($originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_SIMPLE));
    }

    public function isDownloadableType($originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_DOWNLOADABLE));
    }

    public function isConfigurableType($originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_CONFIGURABLE));
    }

    public function isBundleType($originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_BUNDLE));
    }

    public function isGroupedType($originType): bool
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_GROUPED));
    }

    // ---------------------------------------

    public function getOriginKnownTypes($byLogicType = null)
    {
        if ($byLogicType && !in_array($byLogicType, $this->getLogicTypes())) {
            throw new Exception('Unknown logic type.');
        }

        $cache = $this->runtimeCache;

        if (!$byLogicType) {
            if ($cache->getValue(__METHOD__)) {
                return $cache->getValue(__METHOD__);
            }

            $originTypes = $this->getOriginTypes();
            foreach ($this->getLogicTypes() as $logicType) {
                $originTypes = array_merge($originTypes, $this->getOriginCustomTypes($logicType));
            }

            $originTypes = array_unique($originTypes);
            $cache->setValue(__METHOD__, $originTypes);

            return $originTypes;
        }

        if ($cache->getValue(__METHOD__ . $byLogicType)) {
            return $cache->getValue(__METHOD__ . $byLogicType);
        }

        $associatedTypes = [
            self::TYPE_SIMPLE => [
                ProductModel::TYPE_SIMPLE_ORIGIN,
                ProductModel::TYPE_VIRTUAL_ORIGIN,
            ],
            self::TYPE_DOWNLOADABLE => [ProductModel::TYPE_DOWNLOADABLE_ORIGIN],
            self::TYPE_CONFIGURABLE => [ProductModel::TYPE_CONFIGURABLE_ORIGIN],
            self::TYPE_BUNDLE => [ProductModel::TYPE_BUNDLE_ORIGIN],
            self::TYPE_GROUPED => [ProductModel::TYPE_GROUPED_ORIGIN],
        ];

        $originTypes = array_unique(
            array_merge(
                $associatedTypes[$byLogicType],
                $this->getOriginCustomTypes($byLogicType)
            )
        );

        $cache->setValue(__METHOD__ . $byLogicType, $originTypes);

        return $originTypes;
    }

    // ---------------------------------------

    public function getOriginCustomTypes($byLogicType)
    {
        if (!in_array($byLogicType, $this->getLogicTypes())) {
            throw new Exception('Unknown logic type.');
        }

        $customTypes = $this->config->getGroupValue(
            "/magento/product/{$byLogicType}_type/",
            'custom_types'
        );

        if (empty($customTypes)) {
            return [];
        }

        $customTypes = explode(',', $customTypes);

        return !empty($customTypes) ? array_map('trim', $customTypes) : [];
    }

    // ---------------------------------------

    public function getLogicTypes()
    {
        return [
            self::TYPE_SIMPLE,
            self::TYPE_DOWNLOADABLE,
            self::TYPE_CONFIGURABLE,
            self::TYPE_BUNDLE,
            self::TYPE_GROUPED,
        ];
    }

    public function getOriginTypes()
    {
        return [
            ProductModel::TYPE_SIMPLE_ORIGIN,
            ProductModel::TYPE_VIRTUAL_ORIGIN,
            ProductModel::TYPE_CONFIGURABLE_ORIGIN,
            ProductModel::TYPE_BUNDLE_ORIGIN,
            ProductModel::TYPE_GROUPED_ORIGIN,
            ProductModel::TYPE_DOWNLOADABLE_ORIGIN,
        ];
    }

    //########################################

    public function getCachedAndLoadedProduct($product, $storeId = null)
    {
        if ($product instanceof \Magento\Catalog\Model\Product) {
            return $product;
        }

        $productId = (int)$product;
        $cacheKey = $productId . '_' . (string)$storeId;

        if (isset($this->cacheLoadedProducts[$cacheKey])) {
            return $this->cacheLoadedProducts[$cacheKey];
        }

        $product = $this->productFactory->create();
        $storeId !== null && $product->setStoreId((int)$storeId);
        $product->load($productId);

        return $this->cacheLoadedProducts[$cacheKey] = $product;
    }

    public function calculateStockAvailability($isInStock, $manageStock, $useConfigManageStock)
    {
        $manageStockGlobal = $this->catalogInventoryConfiguration->getManageStock();
        if (($useConfigManageStock && !$manageStockGlobal) || (!$useConfigManageStock && !$manageStock)) {
            return true;
        }

        return (bool)$isInStock;
    }

    /**
     * @throws \M2E\Otto\Model\Exception\Logic
     */
    public function prepareAssociatedProducts(
        array $associatedProducts,
        \M2E\Otto\Model\Magento\Product $product
    ): array {
        $productType = $product->getTypeId();
        $productId = $product->getProductId();

        if (
            $this->isSimpleType($productType) ||
            $this->isDownloadableType($productType)
        ) {
            return [$productId];
        }

        if ($this->isBundleType($productType)) {
            $bundleAssociatedProducts = [];

            foreach ($associatedProducts as $key => $productIds) {
                $bundleAssociatedProducts[$key] = reset($productIds);
            }

            return $bundleAssociatedProducts;
        }

        if ($this->isConfigurableType($productType)) {
            $configurableAssociatedProducts = [];

            foreach ($associatedProducts as $productIds) {
                if (count($configurableAssociatedProducts) == 0) {
                    $configurableAssociatedProducts = $productIds;
                } else {
                    $configurableAssociatedProducts = array_intersect($configurableAssociatedProducts, $productIds);
                }
            }

            if (count($configurableAssociatedProducts) != 1) {
                throw new \M2E\Otto\Model\Exception\Logic(
                    'There is no associated Product found for Configurable Product.'
                );
            }

            return $configurableAssociatedProducts;
        }

        if ($this->isGroupedType($productType)) {
            return array_values($associatedProducts);
        }

        return [];
    }
}
