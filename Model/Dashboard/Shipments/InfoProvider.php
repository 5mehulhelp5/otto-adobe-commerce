<?php

declare(strict_types=1);

namespace M2E\Otto\Model\Dashboard\Shipments;

class InfoProvider implements \M2E\Core\Model\Dashboard\Shipments\InfoProviderInterface
{
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory;

    public function __construct(
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory
    ) {
        $this->urlHelper = $urlHelper;
        $this->dateRangeFactory = $dateRangeFactory;
    }

    public function getUrlForLateShipments(): string
    {
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();

        return $this->getUrl([
            'status' => \M2E\Otto\Model\Order::STATUS_UNSHIPPED,
            'shipping_date_to[to]' => \M2E\Core\Helper\Date::convertToLocalFormat($currentDate),
            'shipping_date_to[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
        ]);
    }

    public function getUrlForShipByToday(): string
    {
        $dateRange = $this->dateRangeFactory->createForToday();
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();

        return $this->getUrl([
            'status' => \M2E\Otto\Model\Order::STATUS_UNSHIPPED,
            'shipping_date_to[from]' => \M2E\Core\Helper\Date::convertToLocalFormat($currentDate),
            'shipping_date_to[to]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateEnd),
            'shipping_date_to[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
        ]);
    }

    public function getUrlForShipByTomorrow(): string
    {
        $dateRange = $this->dateRangeFactory->createForTomorrow();

        return $this->getUrl([
            'status' => \M2E\Otto\Model\Order::STATUS_UNSHIPPED,
            'shipping_date_to[from]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateStart),
            'shipping_date_to[to]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateEnd),
            'shipping_date_to[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
        ]);
    }

    public function getUrlForTwoAndMoreDays(): string
    {
        $dateRange = $this->dateRangeFactory->createForTwoAndMoreDays();

        return $this->getUrl([
            'status' => \M2E\Otto\Model\Order::STATUS_UNSHIPPED,
            'shipping_date_to[from]' => \M2E\Core\Helper\Date::convertToLocalFormat($dateRange->dateStart),
            'shipping_date_to[locale]' => \M2E\Core\Helper\Date::getLocaleResolver()->getLocale(),
        ]);
    }

    private function getUrl(array $filterParams): string
    {
        return $this->urlHelper->getUrlWithFilter('m2e_otto/otto_order/index', $filterParams);
    }
}
