<?php

declare(strict_types=1);

namespace M2E\Otto\Model;

use M2E\Otto\Model\ResourceModel\ScheduledAction as ScheduledActionResource;

class ScheduledAction extends \M2E\Otto\Model\ActiveRecord\AbstractModel
{
    private \M2E\Otto\Model\Product $listingProduct;
    private Product\Repository $listingProductRepository;

    public function __construct(
        Product\Repository $listingProductRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);

        $this->listingProductRepository = $listingProductRepository;
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(ResourceModel\ScheduledAction::class);
    }

    public function init(
        \M2E\Otto\Model\Product $listingProduct,
        int $action,
        int $statusChanger,
        array $data,
        bool $isForce = false,
        array $tags = [],
        ?\M2E\Otto\Model\Otto\Listing\Product\Action\Configurator $configurator = null
    ): self {
        $this->validateAction($action);
        $this->validateStatusChanger($statusChanger);

        if ($configurator !== null) {
            $data['configurator'] = [
                'allowed_data_types' => $configurator->getAllowedDataTypes()
            ];
        }

        $this
            ->setData(ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID, $listingProduct->getId())
            ->setData(ScheduledActionResource::COLUMN_ACTION_TYPE, $action)
            ->setData(ScheduledActionResource::COLUMN_STATUS_CHANGER, $statusChanger)
            ->setData(ScheduledActionResource::COLUMN_IS_FORCE, (int)$isForce)
            ->setData(ScheduledActionResource::COLUMN_TAG, empty($tags) ? null : implode('/', $tags))
            ->setData(ScheduledActionResource::COLUMN_ADDITIONAL_DATA, json_encode($data, JSON_THROW_ON_ERROR));

        return $this;
    }

    // ----------------------------------------

    public function getListingProduct(): \M2E\Otto\Model\Product
    {
        if ($this->isObjectNew()) {
            throw new \M2E\Otto\Model\Exception\Logic('Model must be loaded.');
        }

        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->listingProduct)) {
            return $this->listingProduct;
        }

        return $this->listingProduct = $this->listingProductRepository->get($this->getListingProductId());
    }

    public function getListingProductId(): int
    {
        return (int)$this->getData(ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID);
    }

    public function getActionType(): int
    {
        return (int)$this->getData(ScheduledActionResource::COLUMN_ACTION_TYPE);
    }

    public function isActionTypeList(): bool
    {
        return $this->getActionType() === \M2E\Otto\Model\Product::ACTION_LIST;
    }

    public function isActionTypeRelist(): bool
    {
        return $this->getActionType() === \M2E\Otto\Model\Product::ACTION_RELIST;
    }

    public function isActionTypeRevise(): bool
    {
        return $this->getActionType() === \M2E\Otto\Model\Product::ACTION_REVISE;
    }

    public function isActionTypeStop(): bool
    {
        return $this->getActionType() === \M2E\Otto\Model\Product::ACTION_STOP;
    }

    public function isActionTypeDelete(): bool
    {
        return $this->getActionType() === \M2E\Otto\Model\Product::ACTION_DELETE;
    }

    public function isForce(): bool
    {
        return (bool)$this->getData(ScheduledActionResource::COLUMN_IS_FORCE);
    }

    public function getTags(): array
    {
        $value = (string)$this->getData(ScheduledActionResource::COLUMN_TAG);

        return explode('/', $value);
    }

    public function getConfigurator(): \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator
    {
        $data = $this->getAdditionalData();

        return \M2E\Otto\Model\Otto\Listing\Product\Action\Configurator::createWithTypes(
            $data['configurator']['allowed_data_types'] ?? [],
        );
    }

    public function getAdditionalData(): array
    {
        $value = $this->getData(ScheduledActionResource::COLUMN_ADDITIONAL_DATA);
        if (empty($value)) {
            return [];
        }

        return (array)json_decode($value, true);
    }

    // ----------------------------------------

    private function validateAction(int $action): void
    {
        $allowedActions = [
            \M2E\Otto\Model\Product::ACTION_LIST,
            \M2E\Otto\Model\Product::ACTION_REVISE,
            \M2E\Otto\Model\Product::ACTION_STOP,
            \M2E\Otto\Model\Product::ACTION_DELETE,
            \M2E\Otto\Model\Product::ACTION_RELIST,
        ];

        if (!in_array($action, $allowedActions, true)) {
            throw new \M2E\Otto\Model\Exception\Logic(
                sprintf('Action %s is not allowed for scheduled.', $action),
            );
        }
    }

    public function getStatusChanger(): int
    {
        return (int)$this->getData(ScheduledActionResource::COLUMN_STATUS_CHANGER);
    }

    private function validateStatusChanger(int $changer): void
    {
        $allowed = [
            \M2E\Otto\Model\Product::STATUS_CHANGER_SYNCH,
            \M2E\Otto\Model\Product::STATUS_CHANGER_USER,
            \M2E\Otto\Model\Product::STATUS_CHANGER_COMPONENT,
            \M2E\Otto\Model\Product::STATUS_CHANGER_OBSERVER,
        ];

        if (!in_array($changer, $allowed)) {
            throw new \M2E\Otto\Model\Exception\Logic(
                sprintf('Status changer %s is not allowed for scheduled.', $changer),
            );
        }
    }
}
