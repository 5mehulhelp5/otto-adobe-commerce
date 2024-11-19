<?php

declare(strict_types=1);

namespace M2E\Otto\Helper;

class Date
{
    private static \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone;
    private static \Magento\Framework\Locale\ResolverInterface $localeResolver;

    /**
     * @param string $date
     *
     * @return \DateTime
     * @throws \Exception
     */
    public static function timezoneDateToGmt(string $date): \DateTime
    {
        $dateObject = self::createDateInCurrentZone($date);
        $timezone = new \DateTimeZone(self::getTimezone()->getDefaultTimezone());
        $dateObject->setTimezone($timezone);

        return $dateObject;
    }

    /**
     * @param string $date
     *
     * @return \DateTime
     * @throws \Exception
     */
    public static function createDateInCurrentZone(string $date): \DateTime
    {
        return new \DateTime($date, new \DateTimeZone(self::getTimezone()->getConfigTimezone()));
    }

    public static function getTimezone(): \Magento\Framework\Stdlib\DateTime\TimezoneInterface
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset(self::$timezone)) {
            return self::$timezone;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone */
        self::$timezone = $objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);

        return self::$timezone;
    }

    /**
     * @param string $localDate
     * @param int|null $localIntlDateFormat
     * @param int|null $localIntlTimeFormat
     * @param string $localTimezone
     *
     * @return false|int
     * @throws \Exception
     */
    public static function parseDateFromLocalFormat(
        string $localDate,
        int $localIntlDateFormat = \IntlDateFormatter::SHORT,
        int $localIntlTimeFormat = \IntlDateFormatter::SHORT,
        string $localTimezone = ''
    ) {
        if ($localTimezone === '') {
            $localTimezone = self::getTimezone()->getConfigTimezone();
        }

        $pattern = '';
        if ($localIntlDateFormat !== \IntlDateFormatter::NONE) {
            $pattern = self::getTimezone()->getDateFormat($localIntlDateFormat);
        }

        if ($localIntlTimeFormat !== \IntlDateFormatter::NONE) {
            $timeFormat = self::getTimezone()->getTimeFormat($localIntlTimeFormat);
            $pattern = empty($pattern) ? $timeFormat : $pattern . ' ' . $timeFormat;
        }

        $formatter = new \IntlDateFormatter(
            self::getLocaleResolver()->getLocale(),
            $localIntlDateFormat,
            $localIntlTimeFormat,
            new \DateTimeZone($localTimezone),
            null,
            $pattern
        );

        return $formatter->parse($localDate);
    }

    public static function getLocaleResolver(): \Magento\Framework\Locale\ResolverInterface
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset(self::$localeResolver)) {
            return self::$localeResolver;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Framework\Locale\ResolverInterface $localeResolver */
        self::$localeResolver = $objectManager->get(\Magento\Framework\Locale\ResolverInterface::class);

        return self::$localeResolver;
    }

    /**
     * Convert DateTime object into a string based on Magento locale settings.
     * Date and time separated by space.
     * If `$dateFormat` set is `\IntlDateFormatter::NONE` - don't print date,
     * if `$timeFormat` set is `\IntlDateFormatter::NONE` - don't print time
     *
     * @param \DateTime $date
     * @param int $dateFormat date format by Intl
     * @param int $timeFormat time format by Intl
     * @param string $localTimezone timezone, see https://www.php.net/manual/en/timezones.php
     *
     * @return string
     * @throws \Exception
     */
    public static function convertToLocalFormat(
        \DateTime $date,
        int $dateFormat = \IntlDateFormatter::SHORT,
        int $timeFormat = \IntlDateFormatter::SHORT,
        string $localTimezone = ''
    ): string {
        if ($localTimezone === '') {
            $localTimezone = self::getTimezone()->getConfigTimezone();
        }

        $localeResolver = self::getLocaleResolver();

        $pattern = '';
        if ($dateFormat !== \IntlDateFormatter::NONE) {
            $pattern = self::getTimezone()->getDateFormat($dateFormat);
        }

        if ($timeFormat !== \IntlDateFormatter::NONE) {
            $pattern .= ' ' . self::getTimezone()->getTimeFormat($timeFormat);
        }

        $formatter = new \IntlDateFormatter(
            $localeResolver->getLocale(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::SHORT,
            new \DateTimeZone($localTimezone),
            null,
            trim($pattern)
        );

        return $formatter->format($date);
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public static function createCurrentGmt(): \DateTime
    {
        return self::createDateGmt('now');
    }

    /**
     * @param string|null $date
     *
     * @return \DateTime
     * @throws \Exception
     */
    public static function createDateGmt(?string $date): \DateTime
    {
        // for backward compatibility
        if ($date === null) {
            $date = 'now';
        }

        return new \DateTime($date, new \DateTimeZone(self::getTimezone()->getDefaultTimezone()));
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public static function createCurrentInCurrentZone(): \DateTime
    {
        return self::createDateInCurrentZone('now');
    }
}
