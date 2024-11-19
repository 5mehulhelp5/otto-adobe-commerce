<?php

namespace M2E\Otto\Observer\Product\AddUpdate;

abstract class AbstractAddUpdate extends \M2E\Otto\Observer\Product\AbstractProduct
{
    private array $affectedListingsProducts = [];
    private \M2E\Otto\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\Otto\Model\Product\Repository $listingProductRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\Otto\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\Otto\Helper\Factory $helperFactory
    ) {
        parent::__construct(
            $productFactory,
            $ourMagentoProductFactory,
            $helperFactory
        );
        $this->listingProductRepository = $listingProductRepository;
    }

    /**
     * @return bool
     */
    public function canProcess(): bool
    {
        return ((string)$this->getEvent()->getProduct()->getSku()) !== '';
    }

    //########################################

    abstract protected function isAddingProductProcess();

    //########################################

    protected function areThereAffectedItems(): bool
    {
        return !empty($this->getAffectedListingsProducts());
    }

    // ---------------------------------------

    /**
     * @return \M2E\Otto\Model\Product[]
     */
    protected function getAffectedListingsProducts(): array
    {
        if (!empty($this->affectedListingsProducts)) {
            return $this->affectedListingsProducts;
        }

        return $this->affectedListingsProducts = $this->listingProductRepository
            ->getItemsByMagentoProductId($this->getProductId());
    }
}
