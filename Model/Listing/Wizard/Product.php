<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Listing\Wizard;

use M2E\Otto\Model\ResourceModel\Listing\Wizard\Product as WizardProductResource;

class Product extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    private \M2E\Otto\Model\Listing\Wizard $wizard;

    private \M2E\Otto\Model\Category\Repository $dictionaryRepository;
    /** @var \M2E\Otto\Model\Listing\Wizard\Repository */
    private Repository $repository;

    public function __construct(
        Repository $repository,
        \M2E\Otto\Model\Category\Repository $dictionaryRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->repository = $repository;
        $this->dictionaryRepository = $dictionaryRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(WizardProductResource::class);
    }

    public function init(\M2E\Otto\Model\Listing\Wizard $wizard, int $magentoProductId): self
    {
        $this
            ->setData(WizardProductResource::COLUMN_WIZARD_ID, $wizard->getId())
            ->setData(WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return $this;
    }

    public function initWizard(\M2E\Otto\Model\Listing\Wizard $wizard): self
    {
        $this->wizard = $wizard;

        return $this;
    }

    public function getWizard(): \M2E\Otto\Model\Listing\Wizard
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->wizard)) {
            $this->wizard = $this->repository->get($this->getWizardId());
        }

        return $this->wizard;
    }

    public function getWizardId(): int
    {
        return (int)$this->getData(WizardProductResource::COLUMN_WIZARD_ID);
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function getUnmanagedProductId(): ?int
    {
        $value = $this->getData(WizardProductResource::COLUMN_UNMANAGED_PRODUCT_ID);

        if ($value === null) {
            return null;
        }

        return (int)$value;
    }

    public function setUnmanagedProductId(int $value): self
    {
        $this->setData(WizardProductResource::COLUMN_UNMANAGED_PRODUCT_ID, $value);

        return $this;
    }

    public function setCategoryId(int $value): self
    {
        $this->setData(WizardProductResource::COLUMN_CATEGORY_ID, $value);

        return $this;
    }

    public function getCategoryDictionaryId(): ?int
    {
        $value = $this->getData(WizardProductResource::COLUMN_CATEGORY_ID);
        if ($value === null) {
            return null;
        }

        return (int)$value;
    }

    public function getCategory(): ?\M2E\Otto\Model\Category
    {
        $categoryId = $this->getCategoryDictionaryId();
        if ($categoryId === null) {
            return null;
        }

        return $this->dictionaryRepository->find($categoryId);
    }

    public function getCategoryId(): ?int
    {
        $category = $this->getCategory();
        if ($category === null) {
            return null;
        }

        return $category->getId();
    }

    public function isProcessed(): bool
    {
        return (bool)$this->getData(WizardProductResource::COLUMN_IS_PROCESSED);
    }

    public function processed(): self
    {
        $this->setData(WizardProductResource::COLUMN_IS_PROCESSED, 1);

        return $this;
    }
}
