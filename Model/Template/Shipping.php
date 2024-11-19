<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Template;

use M2E\Otto\Model\ResourceModel\Template\Shipping as ShippingResource;

class Shipping extends \M2E\Otto\Model\ActiveRecord\AbstractModel implements PolicyInterface
{
    public const HANDLING_TIME_MODE_VALUE = 1;
    public const HANDLING_TIME_MODE_ATTRIBUTE = 2;

    public const DELIVERY_TYPE_PARCEL = 'PARCEL';
    public const DELIVERY_TYPE_FORWARDER_PREFERREDLOCATION = 'FORWARDER_PREFERREDLOCATION';
    public const DELIVERY_TYPE_FORWARDER_CURBSIDE = 'FORWARDER_CURBSIDE';
    public const DELIVERY_TYPE_FORWARDER_HEAVYDUTY = 'FORWARDER_HEAVYDUTY';

    private \M2E\Otto\Model\Account\Repository $accountRepository;
    private \M2E\Otto\Model\Account $account;

    public function __construct(
        \M2E\Otto\Model\Account\Repository $accountRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct(
            $context,
            $registry
        );

        $this->accountRepository = $accountRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ShippingResource::class);
    }

    public function create(
        int $accountId,
        string $title,
        int $handlingTime,
        int $transportTime,
        string $orderCutoff,
        array $workingDays,
        string $type,
        string $shippingProfileId
    ): self {
        $this->setData(ShippingResource::COLUMN_ACCOUNT_ID, $accountId)
             ->setTitle($title)
             ->setShippingProfileId($shippingProfileId)
             ->setHandlingTimeValue($handlingTime)
             ->setTransportTime($transportTime)
             ->setOrderCutoff($orderCutoff)
             ->setWorkingDays($workingDays)
             ->setType($type);

        return $this;
    }

    public function getNick(): string
    {
        return \M2E\Otto\Model\Otto\Template\Manager::TEMPLATE_SHIPPING;
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ShippingResource::COLUMN_TITLE);
    }

    public function setTitle(string $title): self
    {
        $this->setData(ShippingResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_ACCOUNT_ID);
    }

    public function getShippingProfileId(): ?string
    {
        return $this->getData(ShippingResource::COLUMN_SHIPPING_PROFILE_ID);
    }

    public function setShippingProfileId(string $shippingProfileId): self
    {
        $this->setData(ShippingResource::COLUMN_SHIPPING_PROFILE_ID, $shippingProfileId);
        $this->setData(ShippingResource::COLUMN_IS_DELETED, 0);

        return $this;
    }

    public function getHandlingTimeValue(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_HANDLING_TIME);
    }

    public function setHandlingTimeValue(int $handlingTime): self
    {
        $this->setData(ShippingResource::COLUMN_HANDLING_TIME, $handlingTime);

        return $this;
    }

    public function getHandlingTimeMode(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_HANDLING_TIME_MODE);
    }

    /** @deprecated */
    public function isHandlingTimeModeAttribute(): bool
    {
        return $this->getHandlingTimeMode() == self::HANDLING_TIME_MODE_ATTRIBUTE;
    }

    /** @deprecated */
    public function getHandlingTimeAttribute(): string
    {
        return $this->getData(ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE);
    }

    public function getType(): string
    {
        return (string)$this->getData(ShippingResource::COLUMN_TYPE);
    }

    public function setType(string $type): self
    {
        $this->setData(ShippingResource::COLUMN_TYPE, $type);

        return $this;
    }

    public function getCreateDate()
    {
        return $this->getData(ShippingResource::COLUMN_CREATE_DATE);
    }

    public function getWorkingDays(): array
    {
        return json_decode($this->getData(ShippingResource::COLUMN_WORKING_DAYS));
    }

    public function setWorkingDays(array $workingDays): self
    {
        $this->setData(ShippingResource::COLUMN_WORKING_DAYS, json_encode($workingDays));

        return $this;
    }

    public function getOrderCutoff(): string
    {
        return $this->getData(ShippingResource::COLUMN_ORDER_CUTOFF);
    }

    public function setOrderCutoff(string $orderCutoff): self
    {
        $this->setData(ShippingResource::COLUMN_ORDER_CUTOFF, $orderCutoff);

        return $this;
    }

    public function getTransportTime(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_TRANSPORT_TIME);
    }

    public function setTransportTime(int $transportTime): self
    {
        $this->setData(ShippingResource::COLUMN_TRANSPORT_TIME, $transportTime);

        return $this;
    }

    public function isShippingProfileDeleted(): bool
    {
        return (int)$this->getData(ShippingResource::COLUMN_IS_DELETED) === 1;
    }

    public function markAsDeleted(): self
    {
        $this->setData(ShippingResource::COLUMN_SHIPPING_PROFILE_ID, null);
        $this->setData(ShippingResource::COLUMN_IS_DELETED, 1);

        return $this;
    }

    public function getAccount(): \M2E\Otto\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        return $this->account = $this->accountRepository->get($this->getAccountId());
    }
}
