<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\ResourceModel\Category as CategoryResource;

class Category extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    public const DRAFT_STATE = 1;
    public const SAVED_STATE = 2;

    /** @var \M2E\Otto\Model\Category\Attribute[]  */
    private array $productAttributes;
    private ?\M2E\Otto\Model\Category\Attribute $attributeMpn = null;
    private ?\M2E\Otto\Model\Category\Attribute $attributeBrand = null;
    private ?\M2E\Otto\Model\Category\Attribute $attributeManufacture = null;
    private \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository;
    private \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService;

    public function __construct(
        \M2E\Otto\Model\Dictionary\Attribute\Repository $attributeDictionaryRepository,
        \M2E\Otto\Model\Category\Attribute\AttributeService $attributeService,
        \M2E\Otto\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Otto\Model\Category\Attribute\Repository $attributeRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->attributeService = $attributeService;
        $this->attributeDictionaryRepository = $attributeDictionaryRepository;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->attributeRepository = $attributeRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(CategoryResource::class);
    }

    public function create(
        string $categoryGroupId,
        string $title,
        ?int $totalProductAttributes,
        ?bool $hasRequiredProductAttributes
    ): Category {
        $this->setState(self::DRAFT_STATE);
        $this->setCategoryGroupId($categoryGroupId);
        $this->setTitle($title);
        $this->setUsedProductAttributes(0);
        $this->setTotalProductAttributes($totalProductAttributes);
        $this->setHasRequiredProductAttributes($hasRequiredProductAttributes);

        return $this;
    }

    public function hasRecordsOfAttributes(): bool
    {
        return $this->attributeRepository->getCountByCategoryId($this->getId()) > 0;
    }

    /**
     * @return \M2E\Otto\Model\Category\Attribute[]
     */
    public function getProductAttributes(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->productAttributes)) {
            return $this->productAttributes;
        }

        return $this->productAttributes = $this->attributeRepository->findProductAttributes(
            (int)$this->getId(),
        );
    }

    public function getMpnAttribute(): ?\M2E\Otto\Model\Category\Attribute
    {
        if (isset($this->attributeMpn)) {
            return $this->attributeMpn;
        }

        return $this->attributeMpn = $this->attributeRepository->findMpnAttribute((int)$this->getId());
    }

    public function getBrandAttribute(): ?\M2E\Otto\Model\Category\Attribute
    {
        if (isset($this->attributeBrand)) {
            return $this->attributeBrand;
        }

        return $this->attributeBrand = $this->attributeRepository->findBrandAttribute((int)$this->getId());
    }

    public function getManufactureAttribute(): ?\M2E\Otto\Model\Category\Attribute
    {
        if (isset($this->attributeManufacture)) {
            return $this->attributeManufacture;
        }

        return $this->attributeManufacture = $this->attributeRepository->findManufactureAttribute((int)$this->getId());
    }

    public function setCategoryGroupId(string $categoryGroupDictionaryId): void
    {
        $this->setData(CategoryResource::COLUMN_CATEGORY_GROUP_ID, $categoryGroupDictionaryId);
    }

    public function getCategoryGroupId(): string
    {
        return $this->getData(CategoryResource::COLUMN_CATEGORY_GROUP_ID);
    }

    public function setState(int $state): void
    {
        $this->setData(CategoryResource::COLUMN_STATE, $state);
    }

    public function getState(): int
    {
        return (int)$this->getData(CategoryResource::COLUMN_STATE);
    }

    public function setTitle(string $title): void
    {
        $this->setData(CategoryResource::COLUMN_TITLE, $title);
    }

    public function getTitle(): string
    {
        return $this->getData(CategoryResource::COLUMN_TITLE);
    }

    public function setUsedProductAttributes(int $count): void
    {
        $this->setData(CategoryResource::COLUMN_USED_PRODUCT_ATTRIBUTES, $count);
    }

    public function getUsedProductAttributes(): int
    {
        return (int)$this->getData(CategoryResource::COLUMN_USED_PRODUCT_ATTRIBUTES);
    }

    public function setTotalProductAttributes(int $totalProductAttributes): void
    {
        $this->setData(CategoryResource::COLUMN_TOTAL_PRODUCT_ATTRIBUTES, $totalProductAttributes);
    }

    public function getTotalProductAttributes(): int
    {
        return (int)$this->getData(CategoryResource::COLUMN_TOTAL_PRODUCT_ATTRIBUTES);
    }

    public function getHasRequiredProductAttributes(): bool
    {
        return (bool)$this->getData(CategoryResource::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES);
    }

    public function setHasRequiredProductAttributes(bool $hasRequiredProductAttributes): void
    {
        $this->setData(CategoryResource::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES, $hasRequiredProductAttributes);
    }

    public function setCreateDate(\DateTime $dateTime): void
    {
        $this->setData(
            CategoryResource::COLUMN_CREATE_DATE,
            $dateTime->format('Y-m-d H:i:s')
        );
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(CategoryResource::COLUMN_CREATE_DATE)
        );
    }

    public function setUpdateDate(\DateTime $dateTime): void
    {
        $this->setData(
            CategoryResource::COLUMN_UPDATE_DATE,
            $dateTime->format('Y-m-d H:i:s')
        );
    }

    public function setIsDeleted(int $isDeleted): void
    {
        $this->setData(CategoryResource::COLUMN_IS_DELETED, $isDeleted);
    }

    public function getIsDeleted(): int
    {
        return (int)$this->getData(CategoryResource::COLUMN_IS_DELETED);
    }

    public function getUpdateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(CategoryResource::COLUMN_UPDATE_DATE)
        );
    }

    public function getRelatedAttributes(): array
    {
        return $this->attributeRepository->findByCategoryId($this->getId());
    }

    public function installStateSaved(): void
    {
        $this->setData(CategoryResource::COLUMN_STATE, self::SAVED_STATE);
    }

    public function isLocked(): bool
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->getSelect()->where('template_category_id = ?', $this->getId());

        return (bool)$collection->getSize();
    }

    public function isAllRequiredAttributesFilled(string $categoryGroupId): bool
    {
        $allAttributes = array_merge(
            $this->attributeDictionaryRepository->getAttributesByCategoryGroupId($categoryGroupId),
            $this->attributeService->createCustomAttributes()
        );

        $requiredAttributeIds = array_map(
            fn($attribute) => $attribute->getId(),
            array_filter(
                $allAttributes,
                fn($attribute) => $attribute->isRequired()
            )
        );

        $filledAttributeIds = array_map(
            fn($attribute) => $attribute->getCategoryGroupAttributeDictionaryId(),
            array_filter(
                $this->getRelatedAttributes(),
                fn($attribute) => !$attribute->isValueModeNone()
            )
        );

        return count(array_diff($requiredAttributeIds, $filledAttributeIds)) === 0;
    }

    public function getTrackedAttributes(): array
    {
        $trackedAttributes = [];
        foreach ($this->getRelatedAttributes() as $attribute) {
            if (!$attribute->isValueModeCustomAttribute()) {
                continue;
            }

            $trackedAttributes[] = $attribute->getCustomAttributeValue();
        }

        return array_unique(array_filter($trackedAttributes));
    }
}
